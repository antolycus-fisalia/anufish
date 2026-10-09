<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GbifService
{
    public function searchSpecies(string $keyword, int $limit = 20, int $page = 1): array
    {
        $keyword = trim($keyword);

        // Validasi kata kunci
        if (mb_strlen($keyword) < 2 || mb_strlen($keyword) > 150) {
            return $this->failure(
                'Kata kunci harus terdiri dari 2 sampai 150 karakter.'
            );
        }

        // Batasi jumlah hasil dan timeout
        $limit = max(1, min($limit, 100));
        $page = max(1, $page);
        $offset = ($page - 1) * $limit;
        $timeout = max(1, (int)config('services.gbif.timeout', 10));

        try {
            $response = Http::baseUrl(
                rtrim(config('services.gbif.base_url'), '/')
            )
                ->acceptJson()
                ->connectTimeout(min(3, $timeout))
                ->timeout($timeout)
                ->retry(
                    2,
                    200,
                    function (Throwable $exception, PendingRequest $request): bool {
                        if ($exception instanceof ConnectionException) {
                            return true;
                        }

                        return $exception instanceof RequestException
                            && (
                                $exception->response->status() === 429
                                || $exception->response->serverError()
                            );
                    },
                    throw: false
                )
                ->get('/species/search', [
                    'q' => trim($keyword),
                    'qField' => 'VERNACULAR',
                    'rank' => 'SPECIES',
                    'status' => 'ACCEPTED',
                    'highertaxon_key' => 1,
                    'extended' => true,
                    'limit' => $limit,
                    'offset' => $offset,
                ]);

            // Tangani kesalahan HTTP
            if ($response->failed()) {
                Log::warning('GBIF API request failed', [
                    'status' => $response->status(),
                ]);

                return $this->failure(
                    'Gagal mengambil data dari layanan GBIF. Coba lagi nanti.'
                );
            }

            $payload = $response->json();

            // Validasi struktur respons API
            if (
                !is_array($payload)
                || !isset($payload['results'])
                || !is_array($payload['results'])
            ) {
                Log::warning('GBIF API returned an invalid response');

                return $this->failure(
                    'Format respons dari layanan GBIF tidak valid.'
                );
            }

            // Normalisasi hasil pencarian
            $results = collect($payload['results'])
                ->filter(fn($species) => is_array($species))
                ->map(fn(array $species) => $this->mapSpecies($species))
                ->values()
                ->all();

            return [
                'success' => true,
                'message' => empty($results)
                    ? 'Data ikan tidak ditemukan, coba kata kunci lain.'
                    : null,
                'data' => $results,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'has_more' => isset($payload['endOfRecords'])
                        ? !$payload['endOfRecords']
                        : (isset($payload['count'])
                            ? $offset + $limit < (int) $payload['count']
                            : count($payload['results']) === $limit),
                ],
            ];
        } catch (ConnectionException $exception) {
            report($exception);

            return $this->failure(
                'Tidak dapat terhubung ke layanan GBIF. Coba lagi nanti.'
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->failure(
                'Gagal memproses data dari layanan GBIF.'
            );
        }
    }

    private function mapSpecies(array $species): array
    {
        $names = $species['vernacularNames'] ?? [];

        if (!is_array($names)) {
            $names = [];
        }

        // Cari nama umum berdasarkan bahasa
        $findName = function (array $languages) use ($names): ?string {
            foreach ($names as $name) {
                if (!is_array($name)) {
                    continue;
                }

                $language = strtolower(
                    trim((string)($name['language'] ?? ''))
                );

                $value = $name['vernacularName'] ?? null;

                if (
                    is_string($value)
                    && trim($value) !== ''
                    && in_array($language, $languages, true)
                ) {
                    return trim($value);
                }
            }

            return null;
        };

        // Prioritas Bahasa Indonesia, kemudian Inggris
        $commonName = $findName(['id', 'ind'])
            ?? $findName(['en', 'eng']);

        return [
            'key' => $species['key'] ?? null,
            'scientific_name' => $species['scientificName'] ?? null,
            'canonical_name' => $species['canonicalName'] ?? null,
            'common_name' => $commonName,

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

    private function failure(string $message): array
    {
        return [
            'success' => false,
            'message' => $message,
            'data' => [],
        ];
    }
}
