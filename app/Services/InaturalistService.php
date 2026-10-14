<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class InaturalistService
{
    public function __construct(
        private InaturalistClient $client,
        private InaturalistTaxonMapper $mapper,
    ) {}

    public function searchSpecies(string $keyword, int $limit = 20, int $page = 1): array
    {
        $keyword = $this->mapper->normalizeText($keyword);
        if (mb_strlen($keyword) < 1 || mb_strlen($keyword) > 150) {
            return $this->failure(__('Masukkan nama ikan, maksimal 150 karakter.'), 422);
        }
        $limit = max(1, min(100, $limit));
        $page = max(1, $page);
        $cacheKey = 'inaturalist:search:v1:'.hash('sha256', json_encode([
            config('services.inaturalist.base_url'), mb_convert_case($keyword, MB_CASE_FOLD, 'UTF-8'), app()->getLocale(), $limit, $page,
        ], JSON_THROW_ON_ERROR));
        if (($cached = Cache::get($cacheKey)) !== null) {
            return $cached;
        }

        $lock = Cache::lock($cacheKey.':lock', (int) ceil($this->client->timeout()) + 5);
        $waitUntil = microtime(true) + $this->client->timeout();
        do {
            if ($lock->get()) {
                try {
                    if (($cached = Cache::get($cacheKey)) !== null) {
                        return $cached;
                    }
                    $cacheable = true;
                    $result = $this->fetchSearch($keyword, $limit, $page, $waitUntil, $cacheable);
                    if ($result['success'] && $cacheable) {
                        Cache::put($cacheKey, $result, now()->addMinutes(max(1, (int) config('services.inaturalist.search_cache_minutes', 30))));
                    }

                    return $result;
                } finally {
                    $lock->release();
                }
            }
            if (($cached = Cache::get($cacheKey)) !== null) {
                return $cached;
            }
            usleep(100000);
        } while (microtime(true) < $waitUntil);

        if (($cached = Cache::get($cacheKey)) !== null) {
            return $cached;
        }

        return $this->failure($this->errorMessage(429), 429);
    }

    private function fetchSearch(string $keyword, int $limit, int $page, float $deadline, bool &$cacheable): array
    {
        $result = $this->client->request('/taxa', ['q' => $keyword, 'rank' => 'species', 'is_active' => 'true', 'locale' => app()->getLocale(), 'all_names' => 'true', 'per_page' => $limit, 'page' => $page], $deadline);
        if ($result['status'] !== 200) {
            return $this->failure($this->errorMessage($result['status']), $result['status']);
        }
        $payload = $result['payload'];
        $taxa = collect($payload['results'])->filter(fn ($taxon) => is_array($taxon) && $this->mapper->isFish($taxon))->unique('id')->values()->all();
        // One batch supplies ancestors and alternative photos, never N calls per card.
        if ($taxa !== []) {
            $details = $this->client->request('/taxa/'.implode(',', array_column($taxa, 'id')), ['locale' => app()->getLocale(), 'all_names' => 'true'], $deadline);
            if ($details['status'] === 200) {
                $enriched = collect($details['payload']['results'])->filter(fn ($taxon) => is_array($taxon) && $this->mapper->isFish($taxon))->keyBy('id');
                $taxa = array_map(fn ($taxon) => isset($enriched[$taxon['id']]) && $this->mapper->isFish($enriched[$taxon['id']]) ? $enriched[$taxon['id']] : $taxon, $taxa);
            } else {
                $cacheable = false;
            }
        }

        $fishes = array_values(array_filter(
            array_map(fn ($taxon) => $this->mapper->mapSpecies($taxon, $keyword), $taxa),
            fn ($fish) => $this->mapper->matchesSearch($fish, $keyword),
        ));

        return ['success' => true, 'status' => 200, 'message' => $fishes === [] ? __('Data ikan tidak ditemukan pada halaman ini, coba halaman berikutnya atau kata kunci lain.') : null,
            'data' => $fishes,
            'pagination' => ['current_page' => $page, 'per_page' => $limit, 'has_more' => isset($payload['total_results']) ? $page * $limit < (int) $payload['total_results'] : count($payload['results']) === $limit]];
    }

    public function taxon(int $id, ?float $deadline = null, ?string $locale = null): array
    {
        if ($id < 1 || $id > 2147483647) {
            return ['status' => 404, 'taxon' => null];
        }
        $result = $this->client->request('/taxa/'.$id, ['locale' => $locale ?? app()->getLocale(), 'all_names' => 'true'], $deadline);
        if ($result['status'] !== 200) {
            return ['status' => $result['status'], 'taxon' => null];
        }
        $taxon = $result['payload']['results'][0] ?? null;
        if (! is_array($taxon)) {
            return ['status' => 404, 'taxon' => null];
        }
        if (! is_int($taxon['id'] ?? null) || ! is_string($taxon['name'] ?? null)) {
            return ['status' => 502, 'taxon' => null];
        }
        if ($taxon['id'] !== $id) {
            return ['status' => 404, 'taxon' => null];
        }

        return $this->mapper->isFish($taxon) ? ['status' => 200, 'taxon' => $taxon] : ['status' => 404, 'taxon' => null];
    }

    /** @return array{status: int, image: ?array} */
    public function licensedPhoto(int $id, float $deadline): array
    {
        $result = $this->client->request('/observations', [
            'taxon_id' => $id,
            'quality_grade' => 'research',
            'photos' => 'true',
            'photo_license' => 'cc0,cc-by,cc-by-sa',
            'per_page' => 1,
        ], $deadline);
        if ($result['status'] !== 200) {
            return ['status' => $result['status'], 'image' => null];
        }

        $image = $this->mapper->observationPhoto($result['payload']['results'], $id);

        return ['status' => $image ? 200 : 404, 'image' => $image];
    }

    public function mapSpecies(array $taxon, string $keyword = ''): array
    {
        return $this->mapper->mapSpecies($taxon, $keyword);
    }

    private function errorMessage(int $status): string
    {
        return $status === 429 ? __('Layanan iNaturalist sedang sibuk. Tunggu beberapa saat lalu coba lagi.') : __('Tidak dapat mengambil data dari iNaturalist. Coba lagi nanti.');
    }

    private function failure(string $message, int $status): array
    {
        return ['success' => false, 'status' => $status, 'message' => $message, 'data' => []];
    }
}
