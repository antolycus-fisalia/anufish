<?php

namespace Tests\Support;

trait InaturalistFixtures
{
    private function taxonFixture(array $overrides = []): array
    {
        return array_replace([
            'id' => 69675, 'name' => 'Thunnus albacares', 'rank' => 'species', 'is_active' => true,
            'ancestor_ids' => [1, 2, 355675, 47178, 1303739, 47266, 69676],
            'ancestors' => [['id' => 1, 'name' => 'Animalia', 'rank' => 'kingdom'], ['id' => 47178, 'name' => 'Actinopterygii', 'rank' => 'class'], ['id' => 47266, 'name' => 'Scombridae', 'rank' => 'family']],
            'names' => [['name' => 'Tuna sirip kuning', 'locale' => 'id'], ['name' => 'Yellowfin tuna', 'locale' => 'en'], ['name' => 'キハダ', 'locale' => 'ja']],
            'default_photo' => ['id' => 123, 'medium_url' => 'https://inaturalist-open-data.s3.amazonaws.com/photos/123/medium.jpg', 'license_code' => 'cc-by-sa', 'attribution_name' => 'Fotografer QA'],
        ], $overrides);
    }
}
