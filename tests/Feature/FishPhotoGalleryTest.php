<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\Support\InaturalistFixtures;
use Tests\TestCase;

class FishPhotoGalleryTest extends TestCase
{
    use InaturalistFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Http::preventStrayRequests();
    }

    private function photo(int $id, string $license = 'cc-by'): array
    {
        return ['id' => $id, 'license_code' => $license,
            'medium_url' => "https://inaturalist-open-data.s3.amazonaws.com/photos/$id/medium.jpg",
            'url' => "https://inaturalist-open-data.s3.amazonaws.com/photos/$id/square.jpg",
            'attribution' => "Photographer $id"];
    }

    private function observations(array $photos, int $taxonId = 69675, int $id = 100): array
    {
        return ['id' => $id, 'taxon' => ['id' => $taxonId], 'photos' => $photos,
            'user' => ['name' => 'Observer'], 'place_guess' => 'Private reef', 'location' => '-8,115'];
    }

    public function test_detail_renders_all_curated_licensed_photos_without_fetching_observations(): void
    {
        Http::fake(['*' => Http::response(['results' => [$this->taxonFixture([
            'default_photo' => $this->photo(1),
            'taxon_photos' => [
                ['taxon_id' => 69675, 'photo' => $this->photo(1)],
                ['taxon_id' => 69675, 'photo' => $this->photo(2, 'cc0')],
                ['taxon_id' => 69675, 'photo' => $this->photo(3, 'cc-by-nc')],
                ['taxon_id' => 99, 'photo' => $this->photo(4)],
                ['taxon_id' => 69675, 'photo' => $this->photo(5) + ['hidden' => true]],
            ],
        ])]])]);
        $response = $this->actingAs(User::factory()->make())->get('/fish/taxa/69675');
        $response->assertOk()->assertSee('Galeri foto')->assertSee('data-gallery-index="1"', false)
            ->assertDontSee('data-gallery-index="2"', false)->assertSee('/photos/2/square.jpg')
            ->assertDontSee('/photos/3/')->assertDontSee('/photos/4/')->assertDontSee('/photos/5/');
        Http::assertSentCount(1);
    }

    public function test_more_photos_are_deduplicated_filtered_and_cached_without_locations(): void
    {
        Http::fake([
            '*taxa/*' => Http::response(['results' => [$this->taxonFixture()]]),
            '*observations*' => Http::response(['results' => [
                $this->observations([$this->photo(10), $this->photo(10), $this->photo(11, 'cc-by-sa'), $this->photo(12, 'cc-by-nc'), $this->photo(13) + ['hidden' => true]]),
                $this->observations([$this->photo(14)], 99),
            ]]),
        ]);
        $this->actingAs(User::factory()->make());
        $response = $this->getJson('/fish/taxa/69675/photos');
        $response->assertOk()->assertJsonCount(2, 'photos')->assertJsonPath('photos.0.credit', 'Photographer 10')
            ->assertJsonPath('photos.0.large_url', 'https://inaturalist-open-data.s3.amazonaws.com/photos/10/large.jpg')
            ->assertJsonPath('photos.1.license.label', 'CC BY-SA 4.0')->assertJsonPath('next_cursor', null)
            ->assertDontSee('Private reef')->assertDontSee('location')->assertDontSee('photos/12/');
        $this->getJson('/fish/taxa/69675/photos')->assertExactJson($response->json());
        Http::assertSentCount(2);
        Http::assertSent(fn ($request) => str_contains($request->url(), '/observations')
            && $request['photo_license'] === 'cc0,cc-by,cc-by-sa' && $request['order_by'] === 'id' && $request['order'] === 'desc');
    }

    public function test_cursor_pagination_continues_even_when_a_batch_has_no_usable_photos(): void
    {
        $rows = array_map(fn ($id) => $this->observations([$this->photo($id, 'cc-by-nc')], 69675, $id), range(100, 89));
        Http::fake([
            '*taxa/*' => Http::response(['results' => [$this->taxonFixture()]]),
            '*observations*' => Http::sequence()->push(['results' => $rows])
                ->push(['results' => [$this->observations([$this->photo(88)], 69675, 88)]]),
        ]);
        $this->actingAs(User::factory()->make())->getJson('/fish/taxa/69675/photos')
            ->assertOk()->assertJsonCount(0, 'photos')->assertJsonPath('next_cursor', 89);
        $this->getJson('/fish/taxa/69675/photos?before=89')->assertOk()
            ->assertJsonCount(1, 'photos')->assertJsonPath('next_cursor', null);
        Http::assertSent(fn ($request) => ($request['id_below'] ?? null) === 89);
    }

    public function test_gallery_requires_login_valid_cursor_and_fish_identity(): void
    {
        Http::fake(['*' => Http::response(['results' => [$this->taxonFixture(['ancestor_ids' => [47126], 'ancestors' => []])]])]);
        $this->getJson('/fish/taxa/69675/photos')->assertUnauthorized();
        $this->actingAs(User::factory()->make());
        foreach (['0', '-1', 'abc', '1.5'] as $cursor) {
            $this->getJson('/fish/taxa/69675/photos?before='.$cursor)->assertUnprocessable();
        }
        Http::assertNothingSent();
        $this->getJson('/fish/taxa/69675/photos')->assertNotFound();
        Http::assertSentCount(1);
    }

    public function test_outage_is_retryable_and_does_not_cache_a_failed_photo_batch(): void
    {
        Http::fake([
            '*taxa/*' => Http::response(['results' => [$this->taxonFixture()]]),
            '*observations*' => Http::sequence()->push([], 503)->push(['results' => [$this->observations([$this->photo(10)])]]),
        ]);
        $this->actingAs(User::factory()->make())->getJson('/fish/taxa/69675/photos')->assertStatus(502);
        $this->getJson('/fish/taxa/69675/photos')->assertOk()->assertJsonCount(1, 'photos');
        Http::assertSentCount(3);
    }
}
