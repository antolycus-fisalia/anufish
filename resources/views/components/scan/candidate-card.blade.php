<article
    class="scan-candidate-card"
    :class="{ 'scan-candidate-card-selected': selectedCandidateIndex === index }"
>
    <div class="min-w-0">
        <h3
            class="scan-candidate-name"
            x-text="candidate.scientific_name"
        ></h3>

        <p
            x-show="candidate.confidence !== null"
            class="scan-candidate-confidence"
        >
            {{ __('Confidence AI:') }}
            <span x-text="formatConfidence(candidate.confidence)"></span>
        </p>

        <p
            x-show="candidate.confidence === null"
            class="scan-candidate-confidence"
        >
            {{ __('Confidence tidak tersedia') }}
        </p>
    </div>

    <button
        x-show="candidates.length > 1"
        type="button"
        class="scan-secondary-action"
        :aria-pressed="selectedCandidateIndex === index"
        @click="selectCandidate(index)"
    >
        <span x-show="selectedCandidateIndex !== index">
            {{ __('Pilih kandidat') }}
        </span>

        <span x-show="selectedCandidateIndex === index">
            {{ __('Kandidat dipilih') }}
        </span>
    </button>
</article>
