<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\Support\InaturalistFixtures;
use Tests\TestCase;

class LocaleAndFishExtrasTest extends TestCase
{
    use InaturalistFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Http::preventStrayRequests();
    }

    public function test_language_switch_preserves_search_and_translates_page(): void
    {
        $this->post('/language', ['locale' => 'en', 'return_to' => '/?q=tuna&page=2'])
            ->assertRedirect('/?q=tuna&page=2')->assertSessionHas('locale', 'en');
        $this->actingAs(User::factory()->make())->get('/')
            ->assertOk()->assertSee('lang="en"', false)->assertSee('Explore Fish');
        Http::assertNothingSent();
    }

    public function test_only_supported_languages_and_local_redirects_are_accepted(): void
    {
        $this->postJson('/language', ['locale' => 'ja'])->assertUnprocessable();
        foreach (['https://example.org', '//example.org', '/\\example.org'] as $target) {
            $this->post('/language', ['locale' => 'id', 'return_to' => $target])->assertRedirect('/');
        }
    }

    public function test_background_extras_use_english_description_and_exact_taxon_licensed_photo(): void
    {
        Http::fake(function ($request) {
            if (str_contains($request->url(), '/observations')) {
                return Http::response(['results' => [
                    ['taxon' => ['id' => 999], 'photos' => [['id' => 10, 'license_code' => 'cc0', 'url' => 'https://static.inaturalist.org/photos/10/square.jpg']]],
                    ['taxon' => ['id' => 69675], 'user' => ['login' => 'Photographer'], 'photos' => [['id' => 11, 'license_code' => 'cc0', 'url' => 'https://inaturalist-open-data.s3.amazonaws.com/photos/11/square.jpg']]],
                ]]);
            }

            return Http::response(['results' => [$this->taxonFixture([
                'default_photo' => null, 'taxon_photos' => [],
                'wikipedia_summary' => $request['locale'] === 'en' ? '<p>English fish description.</p>' : null,
            ])]]);
        });
        $this->actingAs(User::factory()->make())->get('/fish/taxa/69675')->assertOk()->assertDontSee('Lokasi pengamatan');
        Http::assertSentCount(1);
        $this->getJson('/fish/taxa/69675/extras')->assertOk()
            ->assertJsonPath('description', 'English fish description.')
            ->assertJsonPath('description_language', 'en')
            ->assertJsonPath('image.url', 'https://inaturalist-open-data.s3.amazonaws.com/photos/11/medium.jpg')
            ->assertJsonPath('image.credit', 'Photographer');
        Http::assertSentCount(3);
        $this->getJson('/fish/taxa/69675/extras')->assertOk();
        Http::assertSentCount(3);
    }

    public function test_english_session_is_sent_to_provider(): void
    {
        Http::fake(['*' => Http::response(['results' => []])]);
        $this->withSession(['locale' => 'en'])->actingAs(User::factory()->make())
            ->getJson('/fish/search?q=tuna')->assertOk();
        Http::assertSent(fn ($request) => $request['locale'] === 'en');
    }
}
