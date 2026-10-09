<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FishSearchTest extends TestCase
{
    public function test_pagination_sends_offset_and_preserves_query_in_links(): void
    {
        Http::fake(['*' => Http::response([
            'results' => [['scientificName' => 'Thunnus albacares']],
            'endOfRecords' => false,
        ])]);

        $this->actingAs(User::factory()->make())
            ->get(route('homepage', ['q' => 'tuna', 'page' => 2]))
            ->assertOk()
            ->assertSee('Halaman 2')
            ->assertSee(route('homepage', ['q' => 'tuna', 'page' => 1]))
            ->assertSee(route('homepage', ['q' => 'tuna', 'page' => 3]));

        Http::assertSent(fn ($request) => $request['offset'] === 20 && $request['limit'] === 20);
    }

    public function test_last_page_has_previous_link_without_next_link(): void
    {
        Http::fake(['*' => Http::response(['results' => [], 'endOfRecords' => true])]);

        $this->actingAs(User::factory()->make())
            ->get(route('homepage', ['q' => 'tuna', 'page' => 2]))
            ->assertOk()
            ->assertSee('Sebelumnya')
            ->assertDontSee('Selanjutnya');
    }

    public function test_invalid_page_is_rejected_before_calling_gbif(): void
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
        Http::fake(['*' => Http::response(['results' => [], 'endOfRecords' => true])]);

        $this->actingAs(User::factory()->make())
            ->getJson(route('fish.search', ['q' => 'tuna', 'page' => 3]))
            ->assertOk()
            ->assertJsonPath('pagination.current_page', 3)
            ->assertJsonPath('pagination.has_more', false);

        Http::assertSent(fn ($request) => $request['offset'] === 40);
    }

    public function test_dashboard_without_query_does_not_call_gbif(): void
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
            'scientificName' => 'Thunnus albacares',
            'family' => 'Scombridae',
        ]]])]);

        $this->actingAs(User::factory()->make())
            ->get(route('homepage', ['q' => 'tuna']))
            ->assertOk()
            ->assertSee('Thunnus albacares')
            ->assertSee('Scombridae');

        Http::assertSent(fn ($request) => $request['q'] === 'tuna');
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

    public function test_invalid_query_does_not_call_gbif(): void
    {
        Http::fake();

        $this->actingAs(User::factory()->make())
            ->from(route('homepage'))
            ->get(route('homepage', ['q' => 'a']))
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
}
