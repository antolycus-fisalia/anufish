<section
    class="scan-panel"
    x-data="scanUpload"
    data-scan
    data-error-format="{{ __('Foto harus berformat JPG, JPEG, atau PNG.') }}"
    data-error-size="{{ __('Ukuran foto maksimal 5 MB.') }}"
    data-error-image="{{ __('File gambar tidak dapat dibaca. Pilih gambar lain yang valid.') }}"
    data-error-request="{{ __('Identifikasi gagal. Silakan coba lagi.') }}"
    data-error-result="{{ __('Hasil identifikasi belum tersedia.') }}"
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
                x-ref="fileInput"
                id="fish_image"
                name="fish_image"
                type="file"
                accept="image/jpeg,image/png"
                class="sr-only"
                @change="handleFile($event)"
            >

            <div class="scan-source-actions">
                <button
                    type="button"
                    class="scan-secondary-action"
                    :disabled="processing"
                    @click="openCamera()"
                >
                    {{ __('Ambil Foto') }}
                </button>

                <button
                    type="button"
                    class="scan-secondary-action"
                    :disabled="processing"
                    @click="openUpload()"
                >
                    {{ __('Unggah Foto') }}
                </button>
            </div>

            <div class="scan-file-info">
                <p>{{ __('Format JPG, JPEG, atau PNG.') }}</p>
                <p>{{ __('Ukuran maksimal 5 MB.') }}</p>
            </div>

            <p
                x-cloak
                x-show="errorMessage"
                x-text="errorMessage"
                class="scan-error"
                role="alert"
            ></p>
        </div>

        <div class="scan-preview-section">
            <div
                x-show="!previewUrl"
                class="scan-preview-placeholder"
            >
                <x-navigation.icon name="scan" />

                <p>{{ __('Preview foto akan tampil di sini.') }}</p>
            </div>

            <img
                x-cloak
                x-show="previewUrl"
                :src="previewUrl"
                class="scan-preview-image"
                alt="{{ __('Preview foto ikan') }}"
            >
        </div>
    </div>

    <div class="scan-footer">
        <p
            x-cloak
            x-show="hasFile"
            x-text="selectedFileName"
            class="scan-selected-file"
        ></p>

        <x-ui.button
            type="button"
            x-bind:disabled="!hasFile || processing"
            @click="processImage()"
        >
            <span x-show="!processing">
                {{ __('Proses Identifikasi') }}
            </span>

            <span
                x-cloak
                x-show="processing"
            >
                {{ __('Memproses...') }}
            </span>
        </x-ui.button>
    </div>
    <x-scan.result-panel />
</section>
