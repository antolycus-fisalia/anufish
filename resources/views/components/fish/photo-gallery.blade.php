@props(['fish'])
<section aria-labelledby="gallery-title" class="fish-gallery"
    data-fish-gallery="{{ json_encode(['photos' => $fish['photos'], 'endpoint' => route('fish.photos', ['taxonId' => $fish['taxon_id']]), 'photoLabel' => __('Foto'), 'species' => $fish['scientific_name'], 'loadLabel' => __('Muat foto lainnya'), 'retryLabel' => __('Coba lagi'), 'loadingLabel' => __('Memuat foto...')]) }}">
    <header class="fish-gallery-heading">
        <h2 id="gallery-title">{{ __('Galeri foto') }}</h2>
        <span data-gallery-count aria-live="polite" aria-atomic="true" class="fish-gallery-count">
            {{ count($fish['photos']) ? '1 / '.count($fish['photos']) : '' }}
        </span>
    </header>
    <figure data-detail-state="photo" @if (!$fish['image']) hidden @endif>
        <button type="button" class="fish-gallery-frame fish-gallery-open" data-viewer-open
            aria-label="{{ __('Perbesar foto') }}: {{ $fish['scientific_name'] }}" aria-haspopup="dialog" aria-controls="fish-photo-viewer">
        <img @if ($fish['image']) src="{{ $fish['image']['url'] }}" @endif data-detail-image
            alt="{{ __('Foto spesies') }}: {{ $fish['scientific_name'] }}" width="640" height="480"
            decoding="async" referrerpolicy="no-referrer" class="fish-gallery-image">
            <span class="fish-gallery-expand" aria-hidden="true"><x-navigation.icon name="expand" />{{ __('Perbesar foto') }}</span>
            <span data-detail-state="image-loading" hidden role="status" class="fish-gallery-image-loading">{{ __('Memuat foto...') }}</span>
        </button>
        <figcaption class="fish-gallery-credit">
            {{ __('Foto:') }} <span data-detail-credit>{{ $fish['image']['credit'] ?? '' }}</span> ·
            <a href="{{ $fish['image']['source_url'] ?? '#' }}" data-detail-source target="_blank" rel="noopener noreferrer">
                iNaturalist<span class="sr-only">{{ __(' (tab baru)') }}</span></a> ·
            <a href="{{ $fish['image']['license']['url'] ?? '#' }}" data-detail-license target="_blank" rel="noopener noreferrer">
                {{ $fish['image']['license']['label'] ?? '' }}</a>
        </figcaption>
    </figure>
    <div data-detail-state="placeholder" @if ($fish['image']) hidden @endif class="fish-gallery-placeholder" role="status">
        <span class="text-anufish-teal" aria-hidden="true"><x-navigation.icon name="fish" /></span>
        <p class="font-semibold text-anufish-navy" data-detail-state="loading" hidden>{{ __('Mencari foto berlisensi...') }}</p>
        <p class="font-semibold text-anufish-navy" data-detail-state="idle">{{ __('Foto belum tersedia') }}</p>
        <p class="font-semibold text-anufish-navy" data-detail-state="photo-error" hidden>{{ __('Foto belum dapat dimuat.') }}</p>
        <p class="max-w-sm text-sm leading-[22px] text-anufish-muted" data-detail-state="idle">
            {{ __('Belum ditemukan foto dengan lisensi penggunaan yang sesuai. Foto lain dapat dilihat di sumber iNaturalist.') }}
        </p>
        <button type="button" data-detail-retry data-detail-state="retry" hidden class="fish-gallery-secondary">{{ __('Coba lagi') }}</button>
    </div>
    <div class="fish-gallery-navigation" data-gallery-navigation @if (count($fish['photos']) < 2) hidden @endif>
        <button type="button" data-gallery-prev aria-label="{{ __('Foto sebelumnya') }}" class="fish-gallery-arrow" disabled>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="m14 6-6 6 6 6" /></svg>
        </button>
        <p class="text-xs text-anufish-muted">{{ __('Pilih foto untuk melihat lebih dekat') }}</p>
        <button type="button" data-gallery-next aria-label="{{ __('Foto berikutnya') }}" class="fish-gallery-arrow">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="m10 6 6 6-6 6" /></svg>
        </button>
    </div>
    <div data-gallery-thumbnails class="fish-gallery-thumbnails" role="group" aria-label="{{ __('Pilih foto spesies') }}">
        @foreach ($fish['photos'] as $index => $image)
            <button type="button" class="fish-gallery-thumbnail" data-gallery-index="{{ $index }}" aria-pressed="{{ $index === 0 ? 'true' : 'false' }}"
                aria-label="{{ __('Foto') }} {{ $index + 1 }}: {{ $fish['scientific_name'] }}">
                <img src="{{ $image['thumbnail_url'] }}" alt="" width="80" height="64" loading="lazy" decoding="async" referrerpolicy="no-referrer">
                <span class="fish-gallery-selected" aria-hidden="true">✓</span>
            </button>
        @endforeach
    </div>
    <footer class="fish-gallery-footer">
        <button type="button" data-gallery-more class="fish-gallery-secondary">{{ __('Muat foto lainnya') }}</button>
        <p data-gallery-loading hidden role="status" class="fish-gallery-note">{{ __('Memuat foto...') }}</p>
        <p data-gallery-error hidden role="alert" class="fish-gallery-note">{{ __('Foto tambahan belum dapat dimuat. Silakan coba lagi.') }}</p>
        <p data-gallery-end hidden role="status" class="fish-gallery-note">{{ __('Semua foto yang tersedia sudah dimuat.') }}</p>
        <p class="fish-gallery-note">{{ __('Hanya foto dengan lisensi penggunaan yang sesuai yang ditampilkan.') }}</p>
    </footer>
    <x-fish.photo-viewer :fish="$fish" />
</section>
