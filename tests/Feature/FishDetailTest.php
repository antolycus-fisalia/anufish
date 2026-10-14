<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\Support\InaturalistFixtures;
use Tests\TestCase;

class FishDetailTest extends TestCase
{
    use InaturalistFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Http::preventStrayRequests();
    }

    private function fakeDetail(array $taxonOverrides = [], ?array $observations = null): void
    {
        Http::fake([
            'api.inaturalist.org/v1/taxa/69675*' => Http::response(['results' => [$this->taxonFixture($taxonOverrides)]]),
            'api.inaturalist.org/v1/observations*' => Http::response(['results' => $observations ?? [['id' => 321, 'taxon' => ['id' => 69675], 'place_guess' => 'Samudra Hindia', 'geoprivacy' => 'open']], 'total_results' => 1]),
        ]);
    }

    public function test_taxon_detail_displays_licensed_photo_and_preserves_search_return(): void
    {
        // Given
        $this->fakeDetail(['wikipedia_summary' => '<p>Ringkasan ikan pelagis.</p>']);
        // When
        $this->actingAs(User::factory()->make())->get(route('fish.show', ['taxonId' => 69675, 'q' => 'キハダ', 'page' => 2]))
            // Then
            ->assertOk()->assertSee('Tuna sirip kuning')->assertSee('Scombridae')->assertSee('Ringkasan ikan pelagis.')
            ->assertDontSee('Lokasi pengamatan')->assertSee('Fotografer QA')->assertSee('CC BY-SA 4.0')
            ->assertSee('https://www.inaturalist.org/photos/123')
            ->assertSee(route('homepage', ['q' => 'キハダ', 'page' => 2]));
        Http::assertSent(fn ($request) => parse_url($request->url(), PHP_URL_PATH) === '/v1/taxa/69675');
        Http::assertNotSent(fn ($request) => str_contains($request->url(), '/observations'));
        Http::assertSentCount(1);
    }

    public function test_alternative_licensed_photo_is_used_when_default_is_restricted(): void
    {
        // Given
        $this->fakeDetail(['default_photo' => ['id' => 1, 'license_code' => 'cc-by-nc', 'medium_url' => 'https://example.org/restricted.jpg', 'attribution_name' => 'Restricted'],
            'taxon_photos' => [['taxon_id' => 69675, 'photo' => ['id' => 2, 'license_code' => 'cc0', 'medium_url' => 'https://example.org/allowed.jpg', 'attribution_name' => 'Allowed']]]]);
        // When / Then
        $this->actingAs(User::factory()->make())->get('/fish/taxa/69675')->assertOk()->assertSee('allowed.jpg')->assertSee('CC0 1.0')->assertDontSee('restricted.jpg');
    }

    public function test_unknown_restricted_and_unrelated_photos_are_not_displayed(): void
    {
        // Given
        $this->fakeDetail(['default_photo' => ['id' => 1, 'license_code' => null, 'medium_url' => 'https://example.org/unknown.jpg'],
            'taxon_photos' => [['taxon_id' => 69675, 'photo' => ['id' => 2, 'license_code' => 'cc-by-nc', 'medium_url' => 'https://example.org/restricted.jpg']], ['taxon_id' => 99, 'photo' => ['id' => 3, 'license_code' => 'cc-by', 'medium_url' => 'https://example.org/wrong.jpg', 'attribution_name' => 'Wrong']]]]);
        // When / Then
        $this->actingAs(User::factory()->make())->get('/fish/taxa/69675')->assertOk()->assertSee('Foto belum tersedia')->assertDontSee('unknown.jpg')->assertDontSee('restricted.jpg')->assertDontSee('wrong.jpg');
    }

    public function test_protected_observation_does_not_expose_place_or_coordinates(): void
    {
        // Given
        $this->fakeDetail([], [['id' => 321, 'taxon' => ['id' => 69675], 'place_guess' => 'Secret reef', 'location' => '-8.123,115.123', 'private_location' => 'secret', 'taxon_geoprivacy' => 'obscured']]);
        // When / Then
        $this->actingAs(User::factory()->make())->get('/fish/taxa/69675')->assertOk()->assertDontSee('Lokasi pengamatan')->assertDontSee('Secret reef')->assertDontSee('-8.123')->assertDontSee('secret');
    }

    public function test_missing_species_has_distinct_not_found_state(): void
    {
        // Given
        Http::fake(['*' => Http::response([], 404)]);
        // When / Then
        $this->actingAs(User::factory()->make())->get('/fish/taxa/69675')->assertNotFound()->assertSee('Ikan tidak ditemukan');
        Http::assertSentCount(1);
    }

    public function test_non_fish_taxon_is_not_a_fish_detail(): void
    {
        // Given
        $this->fakeDetail(['ancestor_ids' => [47126], 'ancestors' => []]);
        // When / Then
        $this->actingAs(User::factory()->make())->get('/fish/taxa/69675')->assertNotFound();
        Http::assertSentCount(1);
    }

    public function test_wrong_taxon_identity_is_rejected(): void
    {
        // Given
        $this->fakeDetail(['id' => 99]);
        // When / Then
        $this->actingAs(User::factory()->make())->get('/fish/taxa/69675')->assertNotFound();
    }

    public function test_core_outage_is_recoverable(): void
    {
        // Given
        Http::fake(['*' => Http::response([], 503)]);
        // When / Then
        $this->actingAs(User::factory()->make())->get('/fish/taxa/69675')->assertStatus(502)->assertSee('Coba lagi');
    }

    public function test_rate_limit_has_distinct_busy_state(): void
    {
        // Given
        Http::fake(['*' => Http::response([], 429, ['Retry-After' => '60'])]);
        // When / Then
        $this->actingAs(User::factory()->make())->get('/fish/taxa/69675')->assertStatus(429)->assertSee('sedang sibuk')->assertSee('Coba lagi');
    }

    public function test_optional_outage_does_not_hide_core_detail(): void
    {
        // Given
        Http::fake(['api.inaturalist.org/v1/taxa/*' => Http::response(['results' => [$this->taxonFixture()]]), '*' => Http::response([], 503)]);
        // When / Then
        $this->actingAs(User::factory()->make())->get('/fish/taxa/69675')->assertOk()->assertSee('Thunnus albacares')->assertSee('Deskripsi');
    }

    public function test_provider_markup_is_never_rendered_as_executable_html(): void
    {
        // Given
        $this->fakeDetail(['wikipedia_summary' => '<img src=x onerror=alert(1)>Safe &lt;script&gt;alert(2)&lt;/script&gt;']);
        // When / Then
        $this->actingAs(User::factory()->make())->get('/fish/taxa/69675')->assertOk()->assertSee('Safe')->assertDontSee('<img src=x', false)->assertDontSee('<script>alert(2)</script>', false);
    }

    public function test_guests_and_invalid_ids_do_not_trigger_provider_calls(): void
    {
        // Given
        Http::fake();
        // When / Then
        $this->get('/fish/taxa/69675')->assertRedirect(route('login'));
        $this->actingAs(User::factory()->make())->get('/fish/taxa/not-an-id')->assertNotFound();
        $this->get('/fish/taxa/0')->assertNotFound();
        $this->get('/fish/2374013')->assertNotFound();
        $this->get('/fish/taxa/9999999999')->assertNotFound();
        Http::assertNothingSent();
    }
}
