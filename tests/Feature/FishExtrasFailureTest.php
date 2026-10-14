<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\Support\InaturalistFixtures;
use Tests\TestCase;

class FishExtrasFailureTest extends TestCase
{
    use InaturalistFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Http::preventStrayRequests();
    }

    private function fakeMissingPhoto(): void
    {
        Http::fake(['api.inaturalist.org/v1/taxa/*' => Http::response(['results' => [$this->taxonFixture([
            'default_photo' => null, 'taxon_photos' => [], 'wikipedia_summary' => 'Existing description.',
        ])]])]);
    }

    public function test_empty_photo_results_are_distinct_from_provider_outage(): void
    {
        $this->fakeMissingPhoto();
        Http::fake(['api.inaturalist.org/v1/observations*' => Http::response(['results' => []])]);
        $this->actingAs(User::factory()->make())->getJson('/fish/taxa/69675/extras')
            ->assertOk()->assertJsonPath('image_status', 404)->assertJsonPath('image', null)
            ->assertJsonPath('description_status', 200)->assertJsonPath('description', 'Existing description.');
    }

    public function test_photo_outage_keeps_description_and_allows_recovery(): void
    {
        $this->fakeMissingPhoto();
        Http::fake(['api.inaturalist.org/v1/observations*' => Http::sequence()
            ->push([], 503)->push(['results' => [[
                'taxon' => ['id' => 69675], 'user' => ['login' => 'Photographer'],
                'photos' => [['id' => 11, 'license_code' => 'cc0', 'url' => 'https://static.inaturalist.org/photos/11/square.jpg']],
            ]]])]);
        $this->actingAs(User::factory()->make())->getJson('/fish/taxa/69675/extras')
            ->assertOk()->assertJsonPath('image_status', 502)->assertJsonPath('image', null)
            ->assertJsonPath('description', 'Existing description.');
        $this->getJson('/fish/taxa/69675/extras')->assertOk()->assertJsonPath('image_status', 200)
            ->assertJsonPath('image.url', 'https://static.inaturalist.org/photos/11/medium.jpg');
        Http::assertSentCount(3);
    }

    public function test_photo_rate_limit_retains_partial_data_and_shared_cooldown(): void
    {
        $this->fakeMissingPhoto();
        Http::fake(['api.inaturalist.org/v1/observations*' => Http::response([], 429, ['Retry-After' => '60'])]);
        $this->actingAs(User::factory()->make())->getJson('/fish/taxa/69675/extras')
            ->assertOk()->assertJsonPath('image_status', 429)->assertJsonPath('description_status', 200);
        $this->getJson('/fish/taxa/69675/extras')->assertOk()->assertJsonPath('image_status', 429);
        Http::assertSentCount(2);
    }

    public function test_english_fallback_failure_does_not_hide_existing_photo(): void
    {
        Http::fake(fn ($request) => $request['locale'] === 'en'
            ? Http::response([], 503)
            : Http::response(['results' => [$this->taxonFixture(['wikipedia_summary' => null])]]));
        $this->actingAs(User::factory()->make())->getJson('/fish/taxa/69675/extras')
            ->assertOk()->assertJsonPath('image_status', 200)->assertJsonPath('description_status', 502)
            ->assertJsonPath('description', '')->assertJsonPath('image.credit', 'Fotografer QA');
        Http::assertSentCount(2);
    }
}
