<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Throwable;

class GbifService
{
    public function searchSpecies(string $keyword, int $limit = 20): array
    {
        try {
            $response = Http::baseUrl(config('services.gbif.base_url'))
                ->acceptJson()
                ->connectTimeout(2)
                ->timeout(4)
                ->retry(2, 200, throw: false)
                ->get('/species/search', [
                    'q' => trim($keyword),
                    'rank' => 'SPECIES',
                    'limit' => $limit,
                ]);

            if ($response->failed()) {
                return [
                    'success' => false,
                    'message' => 'Gagal mengambil data dari layanan GBIF. Coba lagi nanti.',
                    'data' => [],
                ];
            }

            $results = $response->json('results', []);

            return [
                'success' => true,
                'message' => empty($results)
                    ? 'Data ikan tidak ditemukan, coba kata kunci lain.'
                    : null,
                'data' => collect($results)
                    ->map(fn (array $species) => $this->mapSpecies($species))
                    ->values()
                    ->all(),
            ];
        } catch (ConnectionException $exception) {
            report($exception);

            return [
                'success' => false,
                'message' => 'Gagal mengambil data dari layanan GBIF. Coba lagi nanti.',
                'data' => [],
            ];
        } catch (Throwable $exception) {
            report($exception);

            return [
                'success' => false,
                'message' => 'Gagal mengambil data dari layanan GBIF. Coba lagi nanti.',
                'data' => [],
            ];
        }
    }

    private function mapSpecies(array $species): array
    {
        $vernacularNames = collect($species['vernacularNames'] ?? []);

        $vernacularName = $vernacularNames->firstWhere('language', 'eng')
            ?? $vernacularNames->first();

        return [
            'key' => $species['key'] ?? null,
            'scientific_name' => $species['scientificName'] ?? null,
            'canonical_name' => $species['canonicalName'] ?? null,
            'common_name' => $vernacularName['vernacularName'] ?? null,

            'taxonomy' => [
                'kingdom' => $species['kingdom'] ?? null,
                'phylum' => $species['phylum'] ?? null,
                'class' => $species['class'] ?? null,
                'order' => $species['order'] ?? null,
                'family' => $species['family'] ?? null,
                'genus' => $species['genus'] ?? null,
                'species' => $species['species'] ?? null,
            ],

            'rank' => $species['rank'] ?? null,
            'status' => $species['taxonomicStatus'] ?? null,
        ];
    }
}
