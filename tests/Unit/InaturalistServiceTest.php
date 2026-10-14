<?php

namespace Tests\Unit;

use App\Services\InaturalistService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\Support\InaturalistFixtures;
use Tests\TestCase;

class InaturalistServiceTest extends TestCase
{
    use InaturalistFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        app()->setLocale('id');
        Http::preventStrayRequests();
    }

    public function test_unicode_query_uses_species_search_and_provider_pagination(): void
    {
        // Given
        Http::fake(['*' => Http::response(['results' => [], 'total_results' => 45])]);
        // When
        $result = app(InaturalistService::class)->searchSpecies('鯛', 20, 2);
        // Then
        $this->assertTrue($result['pagination']['has_more']);
        Http::assertSent(fn ($request) => parse_url($request->url(), PHP_URL_HOST) === 'api.inaturalist.org'
            && parse_url($request->url(), PHP_URL_PATH) === '/v1/taxa'
            && $request['q'] === '鯛' && $request['rank'] === 'species' && $request['page'] === 2
            && $request['per_page'] === 20 && $request['all_names'] === 'true' && $request['locale'] === 'id');
    }

    public function test_verified_fish_clades_are_retained_and_non_fish_are_rejected(): void
    {
        // Given: includes sharks, lungfish, coelacanths, jawless fish and non-fish.
        $rows = [];
        foreach ([47178, 196614, 49099, 49231, 85509, 85511] as $id) {
            $rows[] = $this->taxonFixture(['id' => $id + 100000, 'ancestor_ids' => [$id], 'ancestors' => []]);
        }
        $rows[] = $this->taxonFixture(['id' => 64119, 'ancestor_ids' => [47126], 'ancestors' => []]);
        $rows[] = $this->taxonFixture(['id' => 10, 'ancestor_ids' => [85497], 'ancestors' => []]);
        $rows[] = $this->taxonFixture(['id' => 11, 'ancestor_ids' => [], 'ancestors' => []]);
        $rows[] = $this->taxonFixture(['id' => 12, 'is_active' => false]);
        $rows[] = $rows[0];
        Http::fake(['*' => Http::response(['results' => $rows])]);
        // When
        $result = app(InaturalistService::class)->searchSpecies('tuna');
        // Then
        $this->assertSame([147178, 296614, 149099, 149231, 185509, 185511], array_column($result['data'], 'taxon_id'));
        Http::assertSentCount(2);
    }

    public function test_only_bilingual_names_are_retained_and_exact_match_precedes_selected_language(): void
    {
        // Given
        $taxon = $this->taxonFixture(['names' => [
            ['name' => 'Madidihang', 'locale' => 'id'], ['name' => 'キハダ', 'locale' => 'ja'],
            ['name' => 'Yellowfin tuna', 'locale' => 'en'], ['name' => 'YELLOWFIN TUNA', 'locale' => 'en'],
            ['name' => 'تونة', 'locale' => 'ar'], ['name' => 'Thunnus albacares', 'locale' => 'sci'],
            ['name' => ' ', 'locale' => 'en'], ['name' => 'Bad', 'is_valid' => false], 'invalid',
        ]]);
        Http::fake(['*' => Http::response(['results' => [$taxon]])]);
        // When
        $fish = app(InaturalistService::class)->searchSpecies('Yellowfin tuna')['data'][0];
        // Then
        $this->assertSame('Yellowfin tuna', $fish['common_name']);
        $this->assertCount(2, $fish['vernacular_names']);
        $this->assertSame('https://www.inaturalist.org/taxa/69675', $fish['source_url']);
        $this->assertSame('Scombridae', $fish['taxonomy']['family']);
    }

    public function test_selected_language_controls_common_name_priority(): void
    {
        $service = app(InaturalistService::class);
        app()->setLocale('id');
        $this->assertSame('Tuna sirip kuning', $service->mapSpecies($this->taxonFixture())['common_name']);
        app()->setLocale('en');
        $this->assertSame('Yellowfin tuna', $service->mapSpecies($this->taxonFixture())['common_name']);
    }

    public function test_scientific_name_is_usable_when_common_names_are_missing(): void
    {
        // Given
        Http::fake(['*' => Http::response(['results' => [$this->taxonFixture(['names' => []])]])]);
        // When
        $fish = app(InaturalistService::class)->searchSpecies('Thunnus albacares')['data'][0];
        // Then
        $this->assertNull($fish['common_name']);
        $this->assertSame('Thunnus albacares', $fish['scientific_name']);
    }

    public function test_cached_search_avoids_outgoing_requests(): void
    {
        // Given
        Http::fake(['*' => Http::response(['results' => [$this->taxonFixture()]])]);
        app(InaturalistService::class)->searchSpecies('tuna');
        // When
        $result = app(InaturalistService::class)->searchSpecies('tuna');
        // Then
        $this->assertSame(69675, $result['data'][0]['taxon_id']);
        Http::assertSentCount(2);
    }

    public function test_connection_failure_is_controlled_without_retry_storm(): void
    {
        // Given
        Http::fake(['*' => Http::failedConnection()]);
        // When
        $result = app(InaturalistService::class)->searchSpecies('tuna');
        // Then
        $this->assertFalse($result['success']);
        $this->assertSame(502, $result['status']);
    }

    public function test_malformed_response_is_controlled(): void
    {
        // Given
        Http::fake(['*' => Http::response(['results' => 'invalid'])]);
        // When
        $result = app(InaturalistService::class)->searchSpecies('tuna');
        // Then
        $this->assertSame(502, $result['status']);
        $this->assertSame([], $result['data']);
    }

    public function test_optional_batch_failure_preserves_search_identity(): void
    {
        // Given
        Http::fake(['api.inaturalist.org/v1/taxa?*' => Http::response(['results' => [$this->taxonFixture()]]), '*' => Http::response([], 503)]);
        // When
        $result = app(InaturalistService::class)->searchSpecies('tuna');
        // Then
        $this->assertTrue($result['success']);
        $this->assertSame(69675, $result['data'][0]['taxon_id']);
    }

    public function test_retry_after_blocks_new_calls_until_cooldown_expires(): void
    {
        // Given
        Http::fake(['*' => Http::response([], 429, ['Retry-After' => '120'])]);
        app(InaturalistService::class)->searchSpecies('tuna');
        // When
        $result = app(InaturalistService::class)->searchSpecies('shark');
        // Then
        $this->assertSame(429, $result['status']);
        Http::assertSentCount(1);
    }

    public function test_shared_minute_limit_blocks_the_next_request(): void
    {
        // Given
        Cache::put('inaturalist:rolling-minute', array_fill(0, 60, microtime(true)), 120);
        Http::fake();
        // When
        $result = app(InaturalistService::class)->searchSpecies('tuna');
        // Then
        $this->assertSame(429, $result['status']);
        Http::assertNothingSent();
    }

    public function test_shared_daily_limit_blocks_the_next_request(): void
    {
        // Given
        Cache::put('inaturalist:day:'.now()->format('Ymd'), 9999, 86400);
        Http::fake();
        // When
        $result = app(InaturalistService::class)->searchSpecies('tuna');
        // Then
        $this->assertSame(429, $result['status']);
        Http::assertNothingSent();
    }

    public function test_blank_keywords_do_not_call_provider(): void
    {
        // Given
        Http::fake();
        // When
        $result = app(InaturalistService::class)->searchSpecies('　 ');
        // Then
        $this->assertSame(422, $result['status']);
        Http::assertNothingSent();
    }

    public function test_fullwidth_query_is_normalized(): void
    {
        // Given
        if (! class_exists(\Normalizer::class)) {
            $this->markTestSkipped('intl extension is required for full-width normalization.');
        }
        Http::fake(['*' => Http::response(['results' => []])]);
        // When
        app(InaturalistService::class)->searchSpecies('　Ｔｕｎａ　');
        // Then
        Http::assertSent(fn ($request) => $request['q'] === 'Tuna');
    }

    public function test_malformed_optional_taxon_fields_do_not_break_valid_identity(): void
    {
        // Given
        $taxon = $this->taxonFixture(['ancestor_ids' => 'bad', 'ancestors' => [['id' => 47178, 'rank' => [], 'name' => 'Actinopterygii']], 'preferred_common_name' => [], 'names' => 'bad', 'default_photo' => ['license_code' => []]]);
        Http::fake(['*' => Http::response(['results' => [$taxon]])]);
        // When
        $result = app(InaturalistService::class)->searchSpecies('Thunnus');
        // Then
        $this->assertTrue($result['success']);
        $this->assertSame(69675, $result['data'][0]['taxon_id']);
        $this->assertNull($result['data'][0]['image']);
    }

    public function test_exhausted_deadline_skips_optional_network_call(): void
    {
        // Given
        Http::fake();
        // When
        $result = app(InaturalistService::class)->licensedPhoto(69675, microtime(true) - 1);
        // Then
        $this->assertSame(['status' => 502, 'image' => null], $result);
        Http::assertNothingSent();
    }
}
