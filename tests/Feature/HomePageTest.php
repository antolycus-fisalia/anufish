<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    public function test_home_has_hero_and_shortcuts_without_a_search_form_or_external_request(): void
    {
        Http::fake();

        $this->actingAs(User::factory()->make())->get(route('homepage'))
            ->assertOk()
            ->assertSee('Kenali ikan.')
            ->assertSee('Bacaan untukmu')
            ->assertSee('Belum ada artikel untuk ditampilkan')
            ->assertSee(route('fish.index'))
            ->assertDontSee('name="q"', false);

        Http::assertNothingSent();
    }

    public function test_empty_exploration_is_a_separate_page_without_the_home_hero(): void
    {
        Http::fake();

        $this->actingAs(User::factory()->make())->get(route('fish.index'))
            ->assertOk()
            ->assertSee('Jelajahi Ikan')
            ->assertSee('name="q"', false)
            ->assertDontSee('Kenali ikan.')
            ->assertDontSee('Bacaan untukmu');

        Http::assertNothingSent();
    }

    public function test_legacy_home_search_redirect_preserves_query_and_page_without_calling_provider(): void
    {
        Http::fake();

        $this->actingAs(User::factory()->make())->get(route('homepage', ['q' => 'tuna', 'page' => 2]))
            ->assertRedirect(route('fish.index', ['q' => 'tuna', 'page' => 2]));

        Http::assertNothingSent();
    }

    public function test_english_home_and_exploration_keep_the_selected_language(): void
    {
        $this->actingAs(User::factory()->make())->withSession(['locale' => 'en'])
            ->get(route('homepage'))->assertOk()->assertSee('Readings for you')->assertSee('Coming soon');
        $this->get(route('fish.index'))->assertOk()->assertSee('Search species by common or scientific name.');
    }

    public function test_exploration_requires_authentication(): void
    {
        $this->get(route('fish.index'))->assertRedirect(route('login'));
    }
}
