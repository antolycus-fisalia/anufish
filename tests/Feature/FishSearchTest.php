<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FishSearchTest extends TestCase
{
    public function test_pagination_sends_page_and_preserves_query_in_links(): void
    {
        Http::fake(['*' => Http::response([
            'results' => [['id' => 69675, 'name' => 'Thunnus albacares', 'rank' => 'species', 'is_active' => true, 'ancestor_ids' => [47178]]],
            'total_results' => 60,
        ])]);

        $this->actingAs(User::factory()->make())
            ->get(route('homepage', ['q' => 'tuna', 'page' => 2]))
            ->assertOk()
            ->assertSee('Halaman 2')
            ->assertSee(route('homepage', ['q' => 'tuna', 'page' => 1]))
            ->assertSee(route('homepage', ['q' => 'tuna', 'page' => 3]));

        Http::assertSent(fn ($request) => parse_url($request->url(), PHP_URL_PATH) === '/v1/taxa' && (string) $request['page'] === '2' && (string) $request['per_page'] === '20');
    }

    public function test_last_page_has_previous_link_without_next_link(): void
    {
        Http::fake(['*' => Http::response(['results' => [], 'total_results' => 0])]);

        $this->actingAs(User::factory()->make())
            ->get(route('homepage', ['q' => 'tuna', 'page' => 2]))
            ->assertOk()
            ->assertSee('Sebelumnya')
            ->assertDontSee('Selanjutnya');
    }

    public function test_invalid_page_is_rejected_before_calling_inaturalist(): void
    {
        Http::fake();

        $this->actingAs(User::factory()->make())
            ->getJson(route('fish.search', ['q' => 'tuna', 'page' => 0]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('page');

        Http::assertNothingSent();
    }

    public function test_json_search_returns_pagination(): void
    {
        Http::fake(['*' => Http::response(['results' => [], 'total_results' => 0])]);

        $this->actingAs(User::factory()->make())
            ->getJson(route('fish.search', ['q' => 'tuna', 'page' => 3]))
            ->assertOk()
            ->assertJsonPath('pagination.current_page', 3)
            ->assertJsonPath('pagination.has_more', false);

        Http::assertSent(fn ($request) => parse_url($request->url(), PHP_URL_PATH) === '/v1/taxa' && (string) $request['page'] === '3');
    }

    public function test_dashboard_without_query_does_not_call_inaturalist(): void
    {
        Http::fake();

        $this->actingAs(User::factory()->make())
            ->get(route('homepage'))
            ->assertOk()
            ->assertSee('Cari ikan favorit Anda');

        Http::assertNothingSent();
    }

    public function test_dashboard_search_displays_species_from_controller(): void
    {
        Http::fake(['*' => Http::response(['results' => [[
            'id' => 69675, 'name' => 'Thunnus albacares', 'rank' => 'species', 'is_active' => true, 'ancestor_ids' => [47178],
            'ancestors' => [['id' => 47266, 'rank' => 'family', 'name' => 'Scombridae']],
            'names' => [['name' => 'Yellowfin tuna', 'locale' => 'en']],
        ]]])]);

        $this->actingAs(User::factory()->make())
            ->get(route('homepage', ['q' => 'tuna']))
            ->assertOk()
            ->assertSee('Thunnus albacares')
            ->assertSee('Scombridae');

        Http::assertSent(fn ($request) => parse_url($request->url(), PHP_URL_PATH) === '/v1/taxa' && $request['q'] === 'tuna');
    }

    public function test_empty_search_results_are_displayed(): void
    {
        Http::fake(['*' => Http::response(['results' => []])]);

        $this->actingAs(User::factory()->make())
            ->get(route('homepage', ['q' => 'unknownfish']))
            ->assertOk()
            ->assertSee('Ikan tidak ditemukan');
    }

    public function test_service_failure_is_displayed(): void
    {
        Http::fake(['*' => Http::response([], 400)]);

        $this->actingAs(User::factory()->make())
            ->get(route('homepage', ['q' => 'tuna']))
            ->assertOk()
            ->assertSee('Gagal mengambil data ikan');
    }

    public function test_invalid_query_does_not_call_inaturalist(): void
    {
        Http::fake();

        $this->actingAs(User::factory()->make())
            ->from(route('homepage'))
            ->get(route('homepage', ['q' => str_repeat('a', 151)]))
            ->assertRedirect(route('homepage'))
            ->assertSessionHasErrors('q');

        Http::assertNothingSent();
    }

    public function test_json_search_endpoint_is_preserved(): void
    {
        Http::fake(['*' => Http::response(['results' => []])]);

        $this->actingAs(User::factory()->make())
            ->getJson(route('fish.search', ['q' => 'tuna']))
            ->assertOk()
            ->assertJson(['success' => true, 'data' => []]);
    }

    public function test_supported_names_render_safely_and_foreign_names_are_hidden(): void
    {
        Http::fake(['*' => Http::response(['results' => [[
            'id' => 123, 'rank' => 'species', 'is_active' => true, 'ancestor_ids' => [47178],
            'name' => 'Pagrus major',
            'names' => [
                ['name' => '鯛', 'locale' => 'ja'],
                ['name' => 'سمك', 'locale' => 'ar'],
                ['name' => '<script>alert(1)</script>', 'locale' => 'en'],
            ],
        ]]])]);

        $this->actingAs(User::factory()->make())
            ->get(route('homepage', ['q' => 'Pagrus']))
            ->assertOk()
            ->assertDontSee('鯛')
            ->assertDontSee('سمك')
            ->assertDontSee('lang="ar"', false)
            ->assertSee('dir="auto"', false)
            ->assertSee('Nama Indonesia dan Inggris')
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);

        Http::assertSent(fn ($request) => parse_url($request->url(), PHP_URL_PATH) === '/v1/taxa' && $request['q'] === 'Pagrus');
    }

    public function test_json_search_accepts_a_single_character(): void
    {
        Http::fake(['*' => Http::response(['results' => []])]);

        $this->actingAs(User::factory()->make())
            ->getJson(route('fish.search', ['q' => '鯛']))
            ->assertOk()
            ->assertJsonPath('success', true);
    }
}
