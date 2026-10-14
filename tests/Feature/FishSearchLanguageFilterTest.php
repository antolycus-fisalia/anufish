<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\Support\InaturalistFixtures;
use Tests\TestCase;

class FishSearchLanguageFilterTest extends TestCase
{
    use InaturalistFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Http::preventStrayRequests();
    }

    public function test_tuna_query_excludes_eels_matching_only_maori_names_in_both_locales(): void
    {
        $eel = $this->taxonFixture([
            'id' => 1001, 'name' => 'Anguilla dieffenbachii', 'preferred_common_name' => 'Tuna',
            'names' => [['name' => 'Tuna', 'locale' => 'mi'], ['name' => 'New Zealand Longfin Eel', 'locale' => 'en']],
        ]);
        Http::fake(['*' => Http::response(['results' => [$eel, $this->taxonFixture()], 'total_results' => 40])]);
        $this->actingAs(User::factory()->make());
        foreach (['id', 'en'] as $locale) {
            $this->withSession(['locale' => $locale])->getJson('/fish/search?q=tuna')
                ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.taxon_id', 69675)
                ->assertJsonPath('pagination.has_more', true);
            $this->get('/?q=tuna')->assertOk()->assertSee('Thunnus albacares')
                ->assertDontSee('Anguilla dieffenbachii')->assertDontSee('New Zealand Longfin Eel');
        }
    }

    public function test_indonesian_english_and_scientific_partial_names_are_case_insensitive(): void
    {
        Http::fake(['*' => Http::response(['results' => [$this->taxonFixture([
            'names' => [['name' => 'Madidihang', 'locale' => 'ind'], ['name' => 'Yellowfin tuna', 'locale' => 'eng']],
        ])]])]);
        $this->actingAs(User::factory()->make());
        foreach ([' MADIDIHANG ', 'yellowfin', 'THUNNUS', 'albacares'] as $keyword) {
            $this->getJson(route('fish.search', ['q' => $keyword]))->assertOk()
                ->assertJsonCount(1, 'data')->assertJsonPath('data.0.taxon_id', 69675);
        }
    }

    public function test_missing_or_invalid_language_evidence_cannot_match_a_common_name(): void
    {
        Http::fake(['*' => Http::response(['results' => [$this->taxonFixture([
            'name' => 'Anguilla australis', 'preferred_common_name' => 'Tuna',
            'names' => [
                ['name' => 'Tuna', 'locale' => 'en', 'is_valid' => false],
                ['name' => 'Tuna'], ['name' => 'Tuna', 'locale' => 'mi'],
            ],
        ])]])]);
        $this->actingAs(User::factory()->make())->getJson('/fish/search?q=tuna')
            ->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/fish/search?q=Anguilla')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_failed_batch_does_not_admit_unverified_foreign_search_hits(): void
    {
        Http::fake([
            'api.inaturalist.org/v1/taxa?*' => Http::response(['results' => [$this->taxonFixture([
                'name' => 'Anguilla australis', 'names' => [], 'preferred_common_name' => 'Tuna',
            ])]]),
            '*' => Http::response([], 503),
        ]);
        $this->actingAs(User::factory()->make())->getJson('/fish/search?q=tuna')
            ->assertOk()->assertJsonCount(0, 'data');
    }
}
