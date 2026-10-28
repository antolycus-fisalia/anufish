<section
    x-cloak
    x-show="resultStatus !== 'idle'"
    class="scan-result-panel"
    aria-live="polite"
>
    {{-- Loading --}}
    <div
        x-show="resultStatus === 'loading'"
        class="scan-result-state"
    >
        <h2>{{ __('Mengidentifikasi ikan...') }}</h2>

        <p>
            {{ __('AI sedang menganalisis foto ikan yang dipilih.') }}
        </p>
    </div>

    {{-- Failure --}}
    <div
        x-show="resultStatus === 'failure'"
        class="scan-result-state scan-result-state-error"
        role="alert"
    >
        <h2>{{ __('Identifikasi gagal') }}</h2>

        <p x-text="resultError"></p>

        <button
            type="button"
            class="scan-secondary-action"
            :disabled="processing"
            @click="processImage()"
        >
            {{ __('Coba lagi') }}
        </button>
    </div>

    {{-- Unknown --}}
    <div
        x-show="resultStatus === 'unknown'"
        class="scan-result-state"
    >
        <h2>{{ __('Gambar belum dapat diidentifikasi') }}</h2>

        <p>
            {{ __('AI tidak menemukan kandidat ikan dari gambar ini.') }}
        </p>

        <button
            type="button"
            class="scan-secondary-action"
            @click="chooseAnotherImage()"
        >
            {{ __('Pilih foto lain') }}
        </button>
    </div>

    {{-- AI result --}}
    <div
        x-show="
            resultStatus === 'success' ||
            resultStatus === 'ambiguous' ||
            resultStatus === 'partial'
        "
        class="scan-result-content"
    >
        <header class="scan-result-header">
            <p class="scan-result-eyebrow">
                {{ __('Hasil identifikasi AI') }}
            </p>

            <h2>{{ __('Kandidat hasil identifikasi') }}</h2>

            <p class="scan-result-note">
                {{ __('Confidence merupakan skor keluaran AI dan bukan verifikasi dari iNaturalist.') }}
            </p>
        </header>

        <div class="scan-candidate-list">
            <template
                x-for="(candidate, index) in candidates"
                :key="`${candidate.scientific_name}-${index}`"
            >
                <x-scan.candidate-card />
            </template>
        </div>

        {{-- Ambiguous --}}
        <div
            x-show="resultStatus === 'ambiguous' && selectedCandidateIndex === null"
            class="scan-result-notice"
        >
            <h3>{{ __('Pilih kandidat yang sesuai') }}</h3>

            <p>
                {{ __('Beberapa kandidat ditemukan. Anufish tidak memilih kandidat secara otomatis.') }}
            </p>
        </div>

        {{-- Partial --}}
        <div
            x-show="resultStatus === 'partial'"
            class="scan-result-notice"
        >
            <h3>{{ __('Detail referensi belum tersedia') }}</h3>

            <p>
                {{ __('Hasil identifikasi AI tetap ditampilkan, tetapi detail iNaturalist belum tersedia.') }}
            </p>
        </div>

        <x-scan.reference-panel />
    </div>
</section>
