<section
    x-cloak
    x-show="resultStatus !== 'idle'"
    class="scan-result-panel"
>
    {{-- Loading --}}
    <x-ui.state
        x-show="resultStatus === 'loading'"
        state="loading"
        :title="__('Mengidentifikasi ikan...')"
        :message="__('AI sedang menganalisis foto ikan yang dipilih.')"
    />

    {{-- Failure --}}
    <x-ui.state
        x-show="resultStatus === 'failure'"
        state="failure"
        :title="__('Identifikasi gagal')"
    >
        <x-slot:body>
            <span x-text="resultError"></span>
        </x-slot:body>

        <button
            type="button"
            class="scan-secondary-action"
            :disabled="processing"
            @click="processImage()"
        >
            {{ __('Coba lagi') }}
        </button>
    </x-ui.state>

    {{-- Unknown --}}
    <x-ui.state
        x-show="resultStatus === 'unknown'"
        state="unknown"
        :title="__('Gambar belum dapat diidentifikasi')"
        :message="__('AI tidak menemukan kandidat ikan dari gambar ini.')"
    >
        <button
            type="button"
            class="scan-secondary-action"
            @click="chooseAnotherImage()"
        >
            {{ __('Pilih foto lain') }}
        </button>
    </x-ui.state>

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

        {{-- Ambiguous candidate --}}
        <div
            x-show="resultStatus === 'ambiguous' && selectedCandidateIndex === null"
            class="scan-result-notice"
        >
            <h3>{{ __('scan.reference.select_title') }}</h3>

            <p>
                {{ __('scan.reference.select_body') }}
            </p>
        </div>

        {{-- Partial reference --}}
        <x-ui.state
            x-show="resultStatus === 'partial' && referenceStatus !== 'loading'"
            state="partial"
            :title="__('Detail referensi belum tersedia')"
            compact
        >
            <x-slot:body>
                <span
                    x-text="referenceError || @js(__('Hasil identifikasi AI tetap ditampilkan, tetapi detail iNaturalist belum tersedia.'))"
                ></span>
            </x-slot:body>

            <button
                type="button"
                class="scan-secondary-action"
                @click="selectCandidate(selectedCandidateIndex)"
            >
                {{ __('scan.reference.retry') }}
            </button>
        </x-ui.state>

        {{-- Reference loading --}}
        <x-ui.state
            x-show="referenceStatus === 'loading'"
            state="loading"
            :title="__('scan.reference.loading')"
            compact
        />

        {{-- Reference success --}}
        <x-ui.state
            x-show="referenceStatus === 'ready'"
            state="success"
            :title="__('Referensi iNaturalist')"
            compact
        >
            <x-slot:body>
                <span x-show="detailTabStatus === 'opened'">
                    {{ __('scan.reference.tab_opened') }}
                </span>

                <span x-show="detailTabStatus === 'blocked'">
                    {{ __('scan.reference.tab_blocked') }}
                </span>
            </x-slot:body>

            <a
                :href="reference ? '/fish/taxa/' + reference.taxon_id : '#'"
                target="_blank"
                rel="noopener noreferrer"
                class="scan-secondary-action"
            >
                {{ __('Lihat detail ikan') }}
            </a>
        </x-ui.state>

        {{-- Ambiguous reference --}}
        <section
            x-show="referenceStatus === 'ambiguous'"
            class="scan-result-notice"
        >
            <h3>{{ __('scan.reference.ambiguous_title') }}</h3>

            <p>{{ __('scan.reference.ambiguous_body') }}</p>

            <div class="scan-candidate-list">
                <template
                    x-for="option in referenceOptions"
                    :key="option.taxon_id"
                >
                    <article class="scan-candidate-card">
                        <div>
                            <h3 x-text="option.scientific_name"></h3>

                            <p x-text="option.common_name ?? ''"></p>

                            <p>
                                {{ __('Taxon ID iNaturalist') }}:
                                <span x-text="option.taxon_id"></span>
                            </p>
                        </div>

                        <button
                            type="button"
                            class="scan-secondary-action"
                            @click="selectReference(option.taxon_id)"
                        >
                            {{ __('scan.reference.choose') }}
                        </button>
                    </article>
                </template>
            </div>
        </section>
    </div>
</section>
