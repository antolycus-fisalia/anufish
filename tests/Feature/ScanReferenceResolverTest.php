<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\InaturalistClient;
use App\Services\RuvicodeService;
use App\Services\ScanReferenceResolver;
use GuzzleHttp\Exception\ConnectTimeoutException;
use GuzzleHttp\Psr7\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ScanReferenceResolverTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Http::preventStrayRequests();
    }

    private function fish(int $id = 10, string $name = 'Channa striata', int $clade = 47178): array
    {
        return ['id' => $id, 'name' => $name, 'rank' => 'species', 'is_active' => true,
            'ancestor_ids' => [$clade], 'ancestors' => [['id' => 20, 'rank' => 'family', 'name' => 'Channidae']],
            'default_photo' => ['id' => 42, 'medium_url' => 'https://static.inaturalist.org/photos/42/medium.jpg',
                'attribution_name' => 'Photographer', 'license_code' => 'cc-by']];
    }

    private function resolve(string $name = 'Channa striata'): array
    {
        return app(ScanReferenceResolver::class)->enrich([['scientific_name' => $name, 'confidence' => 0.85]])[0];
    }

    public function test_selected_candidate_can_retry_after_provider_failure(): void
    {
        Http::fake(['*' => Http::sequence()->push([], 503)
            ->push(['results' => [$this->fish()]])->push(['results' => [$this->fish()]])]);
        $this->actingAs(User::factory()->make());
        $this->getJson('/scan/reference?scientific_name=Channa%20striata')
            ->assertOk()->assertJsonPath('data.match_status', 'unavailable')
            ->assertJsonStructure(['data' => ['reference_error']]);
        $this->getJson('/scan/reference?scientific_name=Channa%20striata')
            ->assertOk()->assertJsonPath('data.reference.taxon_id', 10);
    }

    public function test_reference_requires_authentication_and_valid_name(): void
    {
        $this->getJson('/scan/reference?scientific_name=Channa')->assertUnauthorized();
        $this->actingAs(User::factory()->make())->getJson('/scan/reference')->assertUnprocessable();
        Http::assertNothingSent();
    }

    public function test_reference_failure_is_translated_for_english_session(): void
    {
        Http::fake(['*' => Http::response(['results' => []])]);
        $this->actingAs(User::factory()->make())->withSession(['locale' => 'en'])
            ->getJson('/scan/reference?scientific_name=Channa%20striata')
            ->assertOk()->assertJsonPath('data.reference_error',
                'No active fish species with the exact scientific name was found on iNaturalist.');
    }

    public function test_reference_reports_timeout_connection_and_missing_species_separately(): void
    {
        app()->setLocale('id');
        Http::fake(['*' => Http::response(['results' => []])]);
        $this->assertStringContainsString('Tidak ditemukan', $this->resolve()['reference_error']);
        Cache::flush();
        Http::swap(new Factory);
        Http::fake(fn () => throw new ConnectionException('connection failed'));
        $this->assertStringContainsString('Koneksi', $this->resolve()['reference_error']);
        Cache::flush();
        Http::swap(new Factory);
        Http::fake(fn () => throw new ConnectionException('timeout', 0,
            new ConnectTimeoutException('timeout', new Request('GET', 'https://api.inaturalist.org'))));
        $this->assertStringContainsString('Waktu', $this->resolve()['reference_error']);
    }

    public function test_exact_active_fish_resolves_after_detail_validation(): void
    {
        $fish = $this->fish();
        Http::fake(['*' => Http::response(['results' => [$fish], 'total_results' => 1])]);
        $result = $this->resolve();
        $this->assertSame('matched', $result['match_status']);
        $this->assertSame(10, $result['taxon_id']);
        $this->assertSame(0.85, $result['confidence']);
        $this->assertSame('Channidae', $result['reference']['taxonomy']['family']);
        $this->assertSame('CC BY 4.0', $result['reference']['image']['license']['label']);
        Http::assertSent(function ($request) {
            parse_str(parse_url($request->url(), PHP_URL_QUERY) ?? '', $query);

            return ($query['q'] ?? null) === 'Channa striata';
        });
        Http::assertSentCount(2);
    }

    public function test_shark_ancestry_is_accepted(): void
    {
        Http::fake(['*' => Http::response(['results' => [$this->fish(40, 'Carcharodon carcharias', 196614)]])]);
        $result = $this->resolve('Carcharodon carcharias');
        $this->assertSame('matched', $result['match_status']);
        $this->assertSame('Chondrichthyes', $result['reference']['taxonomy']['class']);
    }

    public function test_near_match_inactive_nonfish_and_higher_rank_are_rejected(): void
    {
        $inactive = $this->fish(11);
        $inactive['is_active'] = false;
        $genus = $this->fish(12);
        $genus['rank'] = 'genus';
        Http::fake(['*' => Http::response(['results' => [
            $this->fish(13, 'Channa striatax'), $inactive, $genus, $this->fish(14, 'Channa striata', 47158),
        ]])]);
        $result = $this->resolve();
        $this->assertSame('unavailable', $result['match_status']);
        $this->assertNull($result['taxon_id']);
        $this->assertSame('Channa striata', $result['scientific_name']);
        $this->assertSame(0.85, $result['confidence']);
        Http::assertSentCount(1);
    }

    public function test_multiple_exact_taxa_remain_ambiguous_and_have_no_selected_id(): void
    {
        $fish = [$this->fish(10), $this->fish(11)];
        Http::fake(['*' => Http::response(['results' => $fish])]);
        $result = $this->resolve();
        $this->assertSame('ambiguous', $result['match_status']);
        $this->assertNull($result['taxon_id']);
        $this->assertNull($result['reference']);
        $this->assertCount(2, $result['reference_options']);
    }

    public function test_empty_and_provider_failures_preserve_candidates(): void
    {
        $status = 404;
        Http::fake(function () use (&$status) {
            return Http::response($status === 404 ? ['results' => []] : [], $status === 404 ? 200 : $status);
        });
        foreach ([404, 429, 502] as $status) {
            Cache::flush();
            $result = $this->resolve();
            $this->assertSame('unavailable', $result['match_status']);
            $this->assertSame($status, $result['reference_http_status']);
            $this->assertNull($result['taxon_id']);
            $this->assertSame(0.85, $result['confidence']);
        }
        Cache::flush();
        Http::swap(new Factory);
        Http::fake(fn () => throw new ConnectionException('timeout'));
        $this->assertSame(502, $this->resolve()['reference_http_status']);
    }

    public function test_detail_identity_change_does_not_select_a_taxon(): void
    {
        Http::fake(['*/taxa?*' => Http::response(['results' => [$this->fish()]]),
            '*/taxa/10?*' => Http::response(['results' => [$this->fish(10, 'Channa micropeltes')]])]);
        $result = $this->resolve();
        $this->assertSame('unavailable', $result['match_status']);
        $this->assertNull($result['taxon_id']);
    }

    public function test_photo_failure_preserves_validated_identity_and_rejects_unlicensed_image(): void
    {
        $fish = $this->fish();
        $fish['default_photo']['license_code'] = 'cc-by-nc';
        Http::fake(['*/taxa*' => Http::response(['results' => [$fish]]), '*/observations?*' => Http::response([], 503)]);
        $result = $this->resolve();
        $this->assertSame('matched', $result['match_status']);
        $this->assertSame(10, $result['taxon_id']);
        $this->assertNull($result['reference']['image']);
    }

    public function test_all_candidates_share_one_deadline_and_duplicate_names_do_not_repeat_queries(): void
    {
        $deadlines = [];
        $this->mock(InaturalistClient::class, function ($mock) use (&$deadlines) {
            $mock->shouldReceive('timeout')->once()->andReturn(4.0);
            $mock->shouldReceive('request')->twice()->andReturnUsing(function ($path, $query, $deadline) use (&$deadlines) {
                $deadlines[] = $deadline;

                return ['status' => 200, 'payload' => ['results' => []]];
            });
        });
        $candidates = [['scientific_name' => 'Channa striata', 'confidence' => null],
            ['scientific_name' => 'Channa striata', 'confidence' => 0.1],
            ['scientific_name' => 'Carcharodon carcharias', 'confidence' => 0.2]];
        $result = app(ScanReferenceResolver::class)->enrich($candidates);
        $this->assertCount(2, $deadlines);
        $this->assertSame($deadlines[0], $deadlines[1]);
        $this->assertNull($result[0]['confidence']);
        $this->assertSame(0.1, $result[1]['confidence']);
    }

    public function test_search_pagination_does_not_hide_another_exact_match(): void
    {
        Http::fake(function ($request) {
            parse_str(parse_url($request->url(), PHP_URL_QUERY) ?? '', $query);
            if (isset($query['page'])) {
                return Http::response(['results' => [$this->fish($query['page'] == 1 ? 10 : 11)], 'total_results' => 101]);
            }

            return Http::response(['results' => [$this->fish(10), $this->fish(11)]]);
        });
        $this->assertSame('ambiguous', $this->resolve()['match_status']);
        Http::assertSentCount(3);
    }

    public function test_upload_returns_ai_candidates_without_contacting_inaturalist(): void
    {
        Storage::fake('local');
        $this->mock(RuvicodeService::class, fn ($mock) => $mock->shouldReceive('identify')->andReturn([
            'success' => true, 'candidates' => [['scientific_name' => 'Channa striata', 'confidence' => null]], 'error' => null,
        ]));
        Http::fake(['*' => Http::response([], 503)]);
        $response = $this->actingAs(User::factory()->make())->postJson('/scan/upload', ['fish_image' => UploadedFile::fake()->image('fish.jpg')]);
        $response->assertCreated()->assertJsonMissingPath('data.candidates.0.match_status')
            ->assertJsonPath('data.candidates.0.scientific_name', 'Channa striata')
            ->assertJsonPath('data.candidates.0.confidence', null);
        Http::assertNothingSent();
        Storage::disk('local')->assertExists($response->json('data.path'));
    }
}
