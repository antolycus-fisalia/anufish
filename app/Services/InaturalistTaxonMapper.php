<?php

namespace App\Services;

class InaturalistTaxonMapper
{
    // Sarcopterygii alone also includes tetrapods: use lungfish/coelacanth orders.
    private const FISH_CLADES = [47178 => 'Actinopterygii', 196614 => 'Chondrichthyes', 49099 => 'Myxini', 49231 => 'Petromyzonti', 85509 => 'Sarcopterygii', 85511 => 'Sarcopterygii'];

    public function observationPhoto(array $observations, int $taxonId): ?array
    {
        return $this->observationPhotos($observations, $taxonId)[0] ?? null;
    }

    public function observationPhotos(array $observations, int $taxonId): array
    {
        $images = [];
        foreach ($observations as $row) {
            if (! is_array($row) || ! is_array($row['taxon'] ?? null) || ($row['taxon']['id'] ?? null) !== $taxonId || ! is_array($row['photos'] ?? null)) {
                continue;
            }
            foreach ($row['photos'] as $photo) {
                if (! is_array($photo)) {
                    continue;
                }
                $url = $this->httpsUrl($photo['url'] ?? null);
                if (! $url || ! preg_match('~^https://(?:static\.inaturalist\.org|inaturalist-open-data\.s3\.amazonaws\.com)/photos/[0-9]+/square\.(jpg|jpeg|png)$~i', $url)) {
                    continue;
                }
                $photo['medium_url'] = preg_replace('~/square\.(jpg|jpeg|png)$~i', '/medium.$1', $url);
                $user = is_array($row['user'] ?? null) ? $row['user'] : [];
                $attribution = ($photo['attribution'] ?? null) === 'no rights reserved' ? null : ($photo['attribution'] ?? null);
                $photo['attribution_name'] = ($photo['attribution_name'] ?? null) ?: ($attribution ?: (($user['name'] ?? null) ?: ($user['login'] ?? null)));
                if ($image = $this->photo(['id' => $taxonId, 'default_photo' => $photo])) {
                    $images[$image['source_url']] ??= $image;
                }
            }
        }

        return array_values($images);
    }

    public function isFish(array $taxon): bool
    {
        if (($taxon['rank'] ?? null) !== 'species' || ($taxon['is_active'] ?? null) !== true || ! is_int($taxon['id'] ?? null) || $taxon['id'] < 1 || ! is_string($taxon['name'] ?? null) || trim($taxon['name']) === '') {
            return false;
        }
        $ids = is_array($taxon['ancestor_ids'] ?? null) ? $taxon['ancestor_ids'] : [];
        foreach (is_array($taxon['ancestors'] ?? null) ? $taxon['ancestors'] : [] as $ancestor) {
            if (is_array($ancestor)) {
                $ids[] = $ancestor['id'] ?? null;
            }
        }

        return array_intersect(array_keys(self::FISH_CLADES), array_filter($ids, 'is_int')) !== [];
    }

    public function mapSpecies(array $taxon, string $keyword = ''): array
    {
        $names = [];
        foreach (is_array($taxon['names'] ?? null) ? $taxon['names'] : [] as $row) {
            if (! is_array($row) || ($row['is_valid'] ?? true) === false || ($row['locale'] ?? '') === 'sci' || ($row['lexicon'] ?? '') === 'scientific-names' || ! is_string($row['name'] ?? null)) {
                continue;
            }
            $name = $this->normalizeText($row['name']);
            if ($name === '') {
                continue;
            }
            $language = is_string($row['locale'] ?? null) && preg_match('/^[a-z]{2,3}(?:-[a-z0-9]{2,8})*$/i', $row['locale']) ? strtolower($row['locale']) : null;
            if (! in_array($language, ['id', 'ind', 'en', 'eng'], true)) {
                continue;
            }
            $label = class_exists(\Locale::class) ? \Locale::getDisplayLanguage($language, app()->getLocale()) : $language;
            $identity = mb_strtolower($name).'|'.$language;
            $names[$identity] ??= ['name' => $name, 'language' => $language, 'language_label' => $label];
        }
        $names = array_values($names);
        $needle = mb_convert_case($this->normalizeText($keyword), MB_CASE_FOLD, 'UTF-8');
        $preferredLanguages = app()->getLocale() === 'en' ? ['en', 'eng'] : ['id', 'ind'];
        $priority = static fn (array $name) => mb_convert_case($name['name'], MB_CASE_FOLD, 'UTF-8') === $needle ? 0 : (in_array($name['language'], $preferredLanguages, true) ? 1 : 2);
        usort($names, static fn ($a, $b) => $priority($a) <=> $priority($b));
        $primary = $names[0] ?? null;
        $taxonomy = array_fill_keys(['kingdom', 'phylum', 'class', 'order', 'family', 'genus', 'species'], null);
        foreach (is_array($taxon['ancestors'] ?? null) ? $taxon['ancestors'] : [] as $ancestor) {
            if (is_array($ancestor) && is_string($ancestor['rank'] ?? null) && array_key_exists($ancestor['rank'], $taxonomy) && is_string($ancestor['name'] ?? null)) {
                $taxonomy[$ancestor['rank']] = $ancestor['name'];
            }
        }
        if ($taxonomy['class'] === null) {
            foreach (self::FISH_CLADES as $id => $class) {
                if (in_array($id, is_array($taxon['ancestor_ids'] ?? null) ? $taxon['ancestor_ids'] : [], true)) {
                    $taxonomy['class'] = $class;
                    break;
                }
            }
        }
        $taxonomy['species'] = $taxon['name'];

        return ['taxon_id' => $taxon['id'], 'scientific_name' => $taxon['name'], 'canonical_name' => $taxon['name'],
            'common_name' => $primary['name'] ?? (is_string($taxon['preferred_common_name'] ?? null) ? $taxon['preferred_common_name'] : null), 'common_name_language' => $primary['language'] ?? null, 'common_name_language_label' => $primary['language_label'] ?? null,
            'vernacular_names' => $names, 'taxonomy' => $taxonomy, 'rank' => $taxon['rank'], 'status' => 'active',
            'source_url' => 'https://www.inaturalist.org/taxa/'.$taxon['id'], 'image' => $this->photo($taxon)];
    }

    public function matchesSearch(array $fish, string $keyword): bool
    {
        $needle = mb_convert_case($this->normalizeText($keyword), MB_CASE_FOLD, 'UTF-8');
        if ($needle === '') {
            return false;
        }
        $names = array_merge([$fish['scientific_name']], array_column($fish['vernacular_names'], 'name'));
        foreach ($names as $name) {
            $normalized = mb_convert_case($this->normalizeText($name), MB_CASE_FOLD, 'UTF-8');
            if (str_contains($normalized, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function photo(array $taxon): ?array
    {
        return $this->photos($taxon)[0] ?? null;
    }

    public function photos(array $taxon): array
    {
        $photos = [$taxon['default_photo'] ?? null];
        foreach (is_array($taxon['taxon_photos'] ?? null) ? $taxon['taxon_photos'] : [] as $row) {
            if (is_array($row) && ($row['taxon_id'] ?? null) === $taxon['id']) {
                $photos[] = $row['photo'] ?? null;
            }
        }
        $licenses = ['cc0' => ['label' => 'CC0 1.0', 'url' => 'https://creativecommons.org/publicdomain/zero/1.0/'], 'cc-by' => ['label' => 'CC BY 4.0', 'url' => 'https://creativecommons.org/licenses/by/4.0/'], 'cc-by-sa' => ['label' => 'CC BY-SA 4.0', 'url' => 'https://creativecommons.org/licenses/by-sa/4.0/']];
        $images = [];
        foreach ($photos as $photo) {
            if (! is_array($photo) || ($photo['hidden'] ?? false) === true) {
                continue;
            }
            $code = is_string($photo['license_code'] ?? null) ? strtolower($photo['license_code']) : '';
            $license = $licenses[$code] ?? null;
            $url = $this->httpsUrl($photo['medium_url'] ?? null);
            $credit = $this->plainText($photo['attribution_name'] ?? $photo['attribution'] ?? null);
            if ($license && $url && is_int($photo['id'] ?? null) && $photo['id'] > 0 && $credit !== '') {
                $thumbnail = preg_match('~^https://(?:static\.inaturalist\.org|inaturalist-open-data\.s3\.amazonaws\.com)/photos/[0-9]+/medium\.(jpg|jpeg|png)$~i', $url)
                    ? preg_replace('~/medium\.(jpg|jpeg|png)$~i', '/square.$1', $url) : $url;
                $large = $thumbnail !== $url ? preg_replace('~/medium\.(jpg|jpeg|png)$~i', '/large.$1', $url) : $url;
                $images[$photo['id']] ??= ['url' => $url, 'thumbnail_url' => $thumbnail, 'large_url' => $large, 'credit' => $credit, 'source' => 'iNaturalist', 'source_url' => 'https://www.inaturalist.org/photos/'.$photo['id'], 'license' => $license];
            }
        }

        return array_values($images);
    }

    public function plainText(mixed $text): string
    {
        return is_string($text) ? trim(strip_tags(html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8'))) : '';
    }

    private function httpsUrl(mixed $url): ?string
    {
        if (! is_string($url) || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }
        $parts = parse_url($url);

        return ($parts['scheme'] ?? '') === 'https' && ! isset($parts['user']) && ! isset($parts['pass']) ? $url : null;
    }

    public function normalizeText(string $text): string
    {
        if (class_exists(\Normalizer::class)) {
            $text = \Normalizer::normalize($text, \Normalizer::FORM_KC) ?: $text;
        }

        return trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    }
}
