<?php

namespace App\Services;

class ScanReferenceResolver
{
    public function __construct(
        private InaturalistClient $client,
        private InaturalistService $species,
        private InaturalistTaxonMapper $mapper,
    ) {}

    public function enrich(array $candidates): array
    {
        $deadline = microtime(true) + $this->client->timeout();
        $resolved = [];

        return array_map(function (array $candidate) use ($deadline, &$resolved) {
            $name = $this->mapper->normalizeText($candidate['scientific_name']);
            $key = mb_convert_case($name, MB_CASE_FOLD, 'UTF-8');
            $resolved[$key] ??= $this->resolve($name, $deadline);

            return array_merge($candidate, $resolved[$key]);
        }, $candidates);
    }

    private function resolve(string $name, float $deadline): array
    {
        $matching = [];
        for ($page = 1; $page <= 3; $page++) {
            $result = $this->client->request('/taxa', [
                'q' => $name, 'rank' => 'species', 'is_active' => 'true',
                'locale' => app()->getLocale(), 'all_names' => 'true', 'per_page' => 100, 'page' => $page,
            ], $deadline);
            if ($result['status'] !== 200) {
                return $this->unavailable($result['status'], $result['reason'] ?? null);
            }
            $rows = $result['payload']['results'];
            foreach ($rows as $taxon) {
                if (is_array($taxon) && $this->exactFish($taxon, $name)) {
                    $matching[$taxon['id']] = $taxon;
                }
            }
            $total = $result['payload']['total_results'] ?? null;
            $more = is_numeric($total) ? $page * 100 < (int) $total : count($rows) === 100;
            if (! $more) {
                break;
            }
            if ($page === 3) {
                return $this->unavailable(502);
            }
        }
        if ($matching === []) {
            return $this->unavailable(404);
        }

        $details = $this->client->request('/taxa/'.implode(',', array_keys($matching)), [
            'locale' => app()->getLocale(), 'all_names' => 'true',
        ], $deadline);
        if ($details['status'] !== 200) {
            return $this->unavailable($details['status'], $details['reason'] ?? null);
        }
        $references = [];
        foreach ($details['payload']['results'] as $taxon) {
            if (! is_array($taxon) || ! $this->exactFish($taxon, $name) || ! isset($matching[$taxon['id']])) {
                continue;
            }
            $references[$taxon['id']] = $this->mapper->mapSpecies($taxon);
        }
        // Missing or changed identities cannot turn an ambiguous search into a unique match.
        if (count($references) !== count($matching)) {
            return $this->unavailable(502);
        }
        if (count($references) > 1) {
            return ['match_status' => 'ambiguous', 'taxon_id' => null, 'reference' => null,
                'reference_options' => array_values($references), 'reference_http_status' => 200];
        }

        $reference = array_values($references)[0];
        if ($reference['image'] === null) {
            $photo = $this->species->licensedPhoto($reference['taxon_id'], $deadline);
            $reference['image'] = $photo['image'];
        }

        return ['match_status' => 'matched', 'taxon_id' => $reference['taxon_id'],
            'reference' => $reference, 'reference_options' => [], 'reference_http_status' => 200];
    }

    private function exactFish(array $taxon, string $name): bool
    {
        return $this->mapper->isFish($taxon)
            && mb_convert_case($this->mapper->normalizeText($taxon['name']), MB_CASE_FOLD, 'UTF-8')
                === mb_convert_case($name, MB_CASE_FOLD, 'UTF-8');
    }

    private function unavailable(int $status, ?string $reason = null): array
    {
        $message = match (true) {
            $reason === 'timeout' => __('scan.reference.timeout'),
            $reason === 'connection' => __('scan.reference.connection'),
            $status === 404 => __('scan.reference.not_found'),
            $status === 429 => __('scan.reference.rate_limit'),
            default => __('scan.reference.provider'),
        };

        return ['match_status' => 'unavailable', 'taxon_id' => null, 'reference' => null,
            'reference_options' => [], 'reference_http_status' => $status, 'reference_error' => $message];
    }
}
