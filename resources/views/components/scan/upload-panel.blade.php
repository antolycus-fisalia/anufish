<section
    class="scan-panel"
    x-data="scanUpload"
    data-scan
    data-detail-loading-url="{{ route('scan.detail-loading') }}"
    data-reference-url="{{ route('scan.reference') }}"
    data-fish-detail-url="{{ route('fish.show', ['taxonId' => '__TAXON_ID__']) }}"
    data-reference-failure="{{ __('scan.reference.failure') }}"
    data-reference-network="{{ __('scan.reference.network') }}"
    data-reference-rate-limit="{{ __('scan.reference.request_limit') }}"
    data-reference-session="{{ __('scan.reference.session') }}"
    data-error-format="{{ __('Foto harus berformat JPG, JPEG, PNG, atau WebP.') }}"
    data-error-size="{{ __('Ukuran foto maksimal 5 MB.') }}"
    data-error-image="{{ __('File gambar tidak dapat dibaca. Pilih gambar lain yang valid.') }}"
    data-error-request="{{ __('Identifikasi gagal. Silakan coba lagi.') }}"
    data-error-result="{{ __('Hasil identifikasi belum tersedia.') }}"
    data-camera-permission="{{ __('scan.camera.permission') }}"
    data-camera-ready="{{ __('scan.camera.ready') }}"
    data-camera-denied="{{ __('scan.camera.denied') }}"
    data-camera-missing="{{ __('scan.camera.missing') }}"
    data-camera-busy="{{ __('scan.camera.busy') }}"
    data-camera-failed="{{ __('scan.camera.failed') }}"
    data-camera-insecure="{{ __('scan.camera.insecure') }}"
    data-camera-unsupported="{{ __('scan.camera.unsupported') }}"
    data-camera-native="{{ __('scan.camera.native') }}"
    @keydown.escape.window="stopCamera()"
    @pagehide.window="stopCamera()"
    @visibilitychange.document="if (document.hidden) stopCamera()"
>
    <p x-cloak x-show="historyUrl" class="scan-result-note" role="status">
        {{ __('history.saved') }}
        <a :href="historyUrl" class="scan-reference-link">{{ __('history.view') }}</a>
    </p>
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
                accept="image/jpeg,image/png,image/webp"
                class="sr-only"
                aria-label="{{ __('Unggah Foto') }}"
                @change="handleFile($event)"
            >
            <input
                x-ref="cameraInput"
                type="file"
                accept="image/*"
                capture="environment"
                class="sr-only"
                aria-label="{{ __('Ambil Foto') }}"
                @change="handleFile($event)"
                @cancel="cameraNotice = ''"
            >

            <div class="scan-source-actions">
                <button
                    type="button"
                    class="scan-secondary-action"
                    x-ref="cameraButton"
                    :disabled="processing || cameraStatus !== 'idle'"
                    :aria-expanded="cameraStatus !== 'idle'"
                    aria-controls="scan-camera"
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

            <p class="scan-result-note">{{ __('scan.camera.hint') }}</p>
            <p x-cloak x-show="cameraNotice" x-text="cameraNotice" class="scan-camera-notice" role="status"></p>
            <p x-cloak x-show="cameraError" x-text="cameraError" class="scan-error" role="alert"></p>

            <div class="scan-file-info">
                <p>{{ __('Format JPG, JPEG, PNG, atau WebP.') }}</p>
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

        <div class="scan-preview-area">
            <div class="scan-preview-section">
                <div
                    x-show="!previewUrl && cameraStatus === 'idle'"
                    class="scan-preview-placeholder"
                >
                    <x-navigation.icon name="scan" />

                    <p>{{ __('Preview foto akan tampil di sini.') }}</p>
                </div>

                <img
                    x-cloak
                    x-show="previewUrl && cameraStatus === 'idle'"
                    :src="previewUrl"
                    class="scan-preview-image"
                    alt="{{ __('Preview foto ikan') }}"
                >
                <video
                    id="scan-camera"
                    x-ref="cameraVideo"
                    x-cloak
                    x-show="cameraStatus !== 'idle'"
                    class="scan-preview-image"
                    autoplay
                    muted
                    playsinline
                    aria-label="{{ __('scan.camera.preview') }}"
                    :aria-busy="cameraStatus === 'requesting'"
                ></video>
            </div>
            <div class="scan-source-actions" x-cloak x-show="cameraStatus !== 'idle'">
                <button type="button" class="scan-secondary-action" :disabled="cameraStatus !== 'ready'" @click="capturePhoto()">
                    {{ __('scan.camera.capture') }}
                </button>
                <button type="button" class="scan-secondary-action" @click="stopCamera(); $refs.cameraButton.focus()">
                    {{ __('scan.camera.close') }}
                </button>
            </div>
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
            x-bind:disabled="!hasFile || processing || cameraStatus !== 'idle'"
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
