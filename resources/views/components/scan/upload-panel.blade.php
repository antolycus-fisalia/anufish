<section
    class="scan-panel"
    data-scan
    data-error-format="{{ __('Foto harus berformat JPG, JPEG, atau PNG.') }}"
    data-error-size="{{ __('Ukuran foto maksimal 5 MB.') }}"
>
    <div class="scan-grid">
        <div class="scan-input-section">
            <div>
                <h2>{{ __('Pilih foto ikan') }}</h2>

                <p>
                    {{ __('Gunakan kamera perangkat atau pilih gambar yang sudah tersimpan.') }}
                </p>
            </div>

            <input
                id="fish_image"
                name="fish_image"
                type="file"
                accept="image/jpeg,image/png"
                class="sr-only"
                data-scan-input
            >

            <div class="scan-source-actions">
                <button
                    type="button"
                    class="scan-secondary-action"
                    data-scan-camera
                >
                    {{ __('Ambil Foto') }}
                </button>

                <button
                    type="button"
                    class="scan-secondary-action"
                    data-scan-upload
                >
                    {{ __('Unggah Foto') }}
                </button>
            </div>

            <div class="scan-file-info">
                <p>{{ __('Format JPG, JPEG, atau PNG.') }}</p>
                <p>{{ __('Ukuran maksimal 5 MB.') }}</p>
            </div>

            <p
                class="scan-error"
                role="alert"
                hidden
                data-scan-error
            ></p>
        </div>

        <div class="scan-preview-section">
            <div class="scan-preview-placeholder" data-scan-placeholder>
                <x-navigation.icon name="scan" />

                <p>{{ __('Preview foto akan tampil di sini.') }}</p>
            </div>

            <img
                class="scan-preview-image"
                alt="{{ __('Preview foto ikan') }}"
                hidden
                data-scan-preview
            >
        </div>
    </div>

    <div class="scan-footer">
        <div>
            <p class="scan-selected-file" hidden data-scan-file-name></p>
        </div>

        <x-ui.button
            type="button"
            disabled
            data-scan-process
        >
            <span data-scan-state="idle">
                {{ __('Proses Identifikasi') }}
            </span>

            <span hidden data-scan-state="loading">
                {{ __('Memproses...') }}
            </span>
        </x-ui.button>
    </div>
</section>
