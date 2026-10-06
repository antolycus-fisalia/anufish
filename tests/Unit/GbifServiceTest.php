<?php

namespace Tests\Unit;

use App\Services\GbifService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GbifServiceTest extends TestCase
{
    public function test_keyword_and_species_rank_are_sent_to_gbif(): void
    {
        Http::fake([
            '*' => Http::response([
                'results' => [],
            ], 200),
        ]);

        app(GbifService::class)->searchSpecies('tuna');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/species/search')
                && str_contains($request->url(), 'q=tuna')
                && str_contains($request->url(), 'rank=SPECIES');
        });
    }

    public function test_successful_response_is_mapped(): void
    {
        Http::fake([
            '*' => Http::response([
                'results' => [
                    [
                        'key' => 123,
                        'scientificName' => 'Thunnus albacares',
                        'canonicalName' => 'Thunnus albacares',
                        'family' => 'Scombridae',
                        'genus' => 'Thunnus',
                        'rank' => 'SPECIES',
                        'taxonomicStatus' => 'ACCEPTED',
                    ],
                ],
            ], 200),
        ]);

        $result = app(GbifService::class)
            ->searchSpecies('tuna');

        $this->assertTrue($result['success']);

        $this->assertSame(
            'Thunnus albacares',
            $result['data'][0]['scientific_name']
        );
    }

    public function test_api_failure_is_handled(): void
    {
        Http::fake([
            '*' => Http::response([], 500),
        ]);

        $result = app(GbifService::class)
            ->searchSpecies('tuna');

        $this->assertFalse($result['success']);
        $this->assertSame([], $result['data']);
    }
}
