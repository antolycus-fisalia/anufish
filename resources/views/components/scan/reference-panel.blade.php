<section
    x-cloak
    x-show="referenceStatus === 'ready' && reference"
    class="scan-reference-panel"
    aria-labelledby="scan-reference-title"
>
    <div class="scan-reference-header">
        <div>
            <p class="scan-result-eyebrow">
                {{ __('Referensi iNaturalist') }}
            </p>

            <h3 id="scan-reference-title">
                {{ __('Detail referensi ikan') }}
            </h3>
        </div>

        <a
            x-show="reference?.source_url"
            :href="reference?.source_url ?? '#'"
            target="_blank"
            rel="noopener noreferrer"
            class="scan-reference-link"
        >
            {{ __('Lihat sumber iNaturalist') }}
        </a>
    </div>

    <div class="scan-reference-grid">
        <div class="scan-reference-photo">
            <img
                x-cloak
                x-show="reference?.image?.url && !referenceImageFailed"
                :src="reference?.image?.url ?? ''"
                :alt="reference?.scientific_name ?? ''"
                x-on:error="referenceImageFailed = true"
            >

            <div
                x-show="!reference?.image?.url || referenceImageFailed"
                class="scan-reference-placeholder"
            >
                <x-navigation.icon name="scan" />

                <p>{{ __('Foto belum tersedia') }}</p>
            </div>
        </div>

        <div class="scan-reference-content">
            <div>
                <p class="scan-reference-label">
                    {{ __('Nama ilmiah') }}
                </p>

                <p
                    class="scan-reference-value italic"
                    x-text="reference?.scientific_name ?? '—'"
                ></p>
            </div>

            <div x-show="reference?.common_name">
                <p class="scan-reference-label">
                    {{ __('Nama umum') }}
                </p>

                <p
                    class="scan-reference-value"
                    x-text="reference?.common_name ?? ''"
                ></p>
            </div>

            <div x-show="reference?.taxon_id">
                <p class="scan-reference-label">
                    {{ __('Taxon ID iNaturalist') }}
                </p>

                <p
                    class="scan-reference-value"
                    x-text="reference?.taxon_id ?? ''"
                ></p>
            </div>

            <div
                x-show="reference?.image?.credit && !referenceImageFailed"
            >
                <p class="scan-reference-label">
                    {{ __('Atribusi foto') }}
                </p>

                <p
                    class="scan-reference-value"
                    x-text="reference?.image?.credit ?? ''"
                ></p>
            </div>

            <div
                x-show="reference?.image?.license?.label && !referenceImageFailed"
            >
                <p class="scan-reference-label">
                    {{ __('Lisensi') }}
                </p>

                <a
                    :href="reference?.image?.license?.url ?? '#'"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="scan-reference-link"
                    x-text="reference?.image?.license?.label ?? ''"
                ></a>
            </div>
        </div>
    </div>
</section>
