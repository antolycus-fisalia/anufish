<?php

namespace App\Services;

class FishDetailService
{
    public function __construct(
        private InaturalistService $inaturalist,
        private InaturalistClient $client,
        private InaturalistTaxonMapper $mapper,
    ) {}

    public function find(int $taxonId): array
    {
        $result = $this->inaturalist->taxon($taxonId);
        if (! $result['taxon']) {
            return ['status' => $result['status'], 'fish' => null];
        }
        $taxon = $result['taxon'];
        $fish = $this->inaturalist->mapSpecies($taxon);
        $fish['photos'] = $this->mapper->photos($taxon);
        $fish['description'] = $this->mapper->plainText($taxon['wikipedia_summary'] ?? null);
        $fish['description_language'] = app()->getLocale();
        $fish['needs_extras'] = ! $fish['image'] || ($fish['description'] === '' && app()->getLocale() === 'id');

        return ['status' => 200, 'fish' => $fish];
    }

    public function enrich(int $taxonId): array
    {
        $deadline = microtime(true) + $this->client->timeout();
        $result = $this->inaturalist->taxon($taxonId, $deadline);
        if (! $result['taxon']) {
            return ['status' => $result['status']];
        }
        $fish = $this->inaturalist->mapSpecies($result['taxon']);
        $description = $this->mapper->plainText($result['taxon']['wikipedia_summary'] ?? null);
        $descriptionStatus = $description === '' ? 404 : 200;
        $language = app()->getLocale();
        if ($description === '' && $language === 'id') {
            $english = $this->inaturalist->taxon($taxonId, $deadline, 'en');
            $description = $this->mapper->plainText($english['taxon']['wikipedia_summary'] ?? null);
            $descriptionStatus = $english['status'] !== 200 ? $english['status'] : ($description === '' ? 404 : 200);
            if ($description !== '') {
                $language = 'en';
            }
        }
        $photo = $fish['image']
            ? ['status' => 200, 'image' => $fish['image']]
            : $this->inaturalist->licensedPhoto($taxonId, $deadline);

        return [
            'status' => 200,
            'image' => $photo['image'],
            'image_status' => $photo['status'],
            'description' => $description,
            'description_status' => $descriptionStatus,
            'description_language' => $language,
        ];
    }

    public function photos(int $taxonId, ?int $before = null): array
    {
        $deadline = microtime(true) + $this->client->timeout();
        $taxon = $this->inaturalist->taxon($taxonId, $deadline);
        if (! $taxon['taxon']) {
            return ['status' => $taxon['status'], 'photos' => [], 'next_cursor' => null];
        }
        $query = ['taxon_id' => $taxonId, 'photos' => 'true', 'photo_license' => 'cc0,cc-by,cc-by-sa',
            'per_page' => 12, 'order_by' => 'id', 'order' => 'desc'];
        if ($before !== null) {
            $query['id_below'] = $before;
        }
        $result = $this->client->request('/observations', $query, $deadline);
        if ($result['status'] !== 200) {
            return ['status' => $result['status'], 'photos' => [], 'next_cursor' => null];
        }
        $rows = $result['payload']['results'];
        $ids = array_filter(array_column($rows, 'id'), fn ($id) => is_int($id) && $id > 0 && ($before === null || $id < $before));

        return ['status' => 200, 'photos' => $this->mapper->observationPhotos($rows, $taxonId),
            'next_cursor' => count($rows) === 12 && $ids !== [] ? min($ids) : null];
    }
}
