@props(['fish'])
<dialog id="fish-photo-viewer" data-photo-viewer class="fish-photo-viewer" aria-labelledby="photo-viewer-title">
    <div class="fish-photo-viewer-panel">
        <header class="fish-photo-viewer-header">
            <div class="min-w-0">
                <h2 id="photo-viewer-title" class="fish-photo-viewer-title">{{ __('Foto spesies') }}</h2>
                <p class="fish-photo-viewer-species"><bdi>{{ $fish['scientific_name'] }}</bdi></p>
            </div>
            <button type="button" data-viewer-close class="fish-gallery-arrow" aria-label="{{ __('Tutup foto') }}" autofocus>
                <x-navigation.icon name="close" />
            </button>
        </header>
        <div class="fish-photo-viewer-stage" data-viewer-stage>
            <img data-viewer-image alt="{{ __('Foto spesies') }}: {{ $fish['scientific_name'] }}" decoding="async" referrerpolicy="no-referrer">
            <p data-viewer-loading hidden role="status" class="fish-photo-viewer-message">{{ __('Memuat foto...') }}</p>
            <div data-viewer-error hidden class="fish-photo-viewer-message" role="alert">
                <p>{{ __('Foto belum dapat dimuat.') }}</p>
                <button type="button" data-viewer-retry class="fish-gallery-secondary">{{ __('Coba lagi') }}</button>
            </div>
        </div>
        <footer class="fish-photo-viewer-footer">
            <nav class="fish-photo-viewer-navigation" aria-label="{{ __('Navigasi foto') }}">
                <button type="button" data-viewer-prev class="fish-gallery-arrow" aria-label="{{ __('Foto sebelumnya') }}">
                    <x-navigation.icon name="chevron-left" />
                </button>
                <span data-viewer-count class="fish-gallery-count" aria-live="polite" aria-atomic="true"></span>
                <button type="button" data-viewer-next class="fish-gallery-arrow" aria-label="{{ __('Foto berikutnya') }}">
                    <x-navigation.icon name="chevron-right" />
                </button>
            </nav>
            <p class="fish-gallery-credit">
                {{ __('Foto:') }} <span data-viewer-credit></span> ·
                <a data-viewer-source target="_blank" rel="noopener noreferrer">iNaturalist<span class="sr-only">{{ __(' (tab baru)') }}</span></a> ·
                <a data-viewer-license target="_blank" rel="noopener noreferrer"></a>
            </p>
        </footer>
    </div>
</dialog>
