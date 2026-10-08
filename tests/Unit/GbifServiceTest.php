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
            parse_str(
                parse_url($request->url(), PHP_URL_QUERY) ?? '',
                $query
            );

            return $request->method() === 'GET'
                && parse_url($request->url(), PHP_URL_PATH)
                === '/v1/species/search'
                && ($query['q'] ?? null) === 'tuna'
                && ($query['rank'] ?? null) === 'SPECIES'
                && ($query['limit'] ?? null) === '20';
        });
    }

    public function test_empty_results_are_handled(): void
    {
        Http::fake([
            '*' => Http::response(['results' => []], 200),
        ]);

        $result = app(GbifService::class)
            ->searchSpecies('unknownfish');

        $this->assertTrue($result['success']);
        $this->assertSame([], $result['data']);
        $this->assertNotEmpty($result['message']);
    }

    public function test_connection_failure_is_handled(): void
    {
        Http::fake([
            '*' => Http::failedConnection(),
        ]);

        $result = app(GbifService::class)
            ->searchSpecies('tuna');

        $this->assertFalse($result['success']);
        $this->assertSame([], $result['data']);
        $this->assertNotEmpty($result['message']);
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
}
