const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png'];
const MAX_IMAGE_SIZE = 5 * 1024 * 1024;

export function scanUpload() {
    let pendingDetailTab = null;

    return {
        previewUrl: null,
        selectedFileName: '',
        hasFile: false,
        errorMessage: '',
        processing: false,
        selectionGeneration: 0,

        resultStatus: 'idle',
        candidates: [],
        selectedCandidateIndex: null,
        reference: null,
        referenceStatus: 'idle',
        referenceImageFailed: false,
        referenceOptions: [],
        referenceError: '',
        referenceGeneration: 0,
        detailTabStatus: 'idle',
        resultError: '',

        openCamera() {
            const input = this.$refs.fileInput;
            input.setAttribute('capture', 'environment');
            input.click();
        },

        openUpload() {
            const input = this.$refs.fileInput;
            input.removeAttribute('capture');
            input.click();
        },

        async handleFile(event) {
            // Mencegah hasil decode file lama menimpa file terbaru
            const generation = ++this.selectionGeneration;

            this.resetResult();

            const input = event.target;
            const file = input.files?.[0];

            if (!file) {
                return;
            }

            const root = input.closest('[data-scan]');
            const validationError = this.validateFile(file, root);

            if (validationError) {
                this.rejectFile(input, validationError);
                return;
            }

            this.resetSelection();
            this.errorMessage = '';

            const imageUrl = URL.createObjectURL(file);
            const imageIsValid = await this.canDecodeImage(imageUrl);

            if (generation !== this.selectionGeneration) {
                URL.revokeObjectURL(imageUrl);
                return;
            }

            if (!imageIsValid) {
                URL.revokeObjectURL(imageUrl);
                this.rejectFile(input, root?.dataset.errorImage ?? '');
                return;
            }

            this.previewUrl = imageUrl;
            this.selectedFileName = file.name;
            this.hasFile = true;
        },

        validateFile(file, root) {
            if (!ALLOWED_IMAGE_TYPES.includes(file.type)) {
                return root?.dataset.errorFormat ?? '';
            }

            if (file.size > MAX_IMAGE_SIZE) {
                return root?.dataset.errorSize ?? '';
            }

            return null;
        },

        canDecodeImage(imageUrl) {
            return new Promise((resolve) => {
                const image = new Image();

                image.onload = () => {
                    resolve(image.naturalWidth > 0 && image.naturalHeight > 0);
                };

                image.onerror = () => {
                    resolve(false);
                };

                image.src = imageUrl;
            });
        },

        rejectFile(input, message) {
            this.resetSelection();
            input.value = '';
            this.errorMessage = message;
        },

        resetSelection() {
            this.clearPreviewUrl();
            this.selectedFileName = '';
            this.hasFile = false;
        },

        resetResult() {
            this.closePendingDetailTab();
            this.detailTabStatus = 'idle';
            this.referenceGeneration += 1;
            this.resultStatus = 'idle';
            this.candidates = [];
            this.selectedCandidateIndex = null;
            this.reference = null;
            this.referenceStatus = 'idle';
            this.referenceImageFailed = false;
            this.referenceOptions = [];
            this.referenceError = '';
            this.resultError = '';
        },

        formatConfidence(confidence) {
            if (
                confidence === null ||
                confidence === undefined ||
                !Number.isFinite(Number(confidence))
            ) {
                return '';
            }

            return `${Math.round(Number(confidence) * 100)}%`;
        },

        async selectCandidate(index) {
            if (
                !Number.isInteger(index) ||
                index < 0 ||
                index >= this.candidates.length
            ) {
                return;
            }
            if (this.referenceStatus === 'loading' && this.selectedCandidateIndex === index) return;

            this.prepareDetailTab();

            this.selectedCandidateIndex = index;
            const generation = ++this.referenceGeneration;
            this.referenceImageFailed = false;
            const candidate = this.candidates[index];
            this.reference = null;
            this.referenceOptions = [];
            this.referenceError = '';
            this.referenceStatus = 'loading';
            this.resultStatus = 'partial';
            const root = this.$refs.fileInput.closest('[data-scan]');
            try {
                const query = new URLSearchParams({ scientific_name: candidate.scientific_name });
                const response = await fetch(`${root?.dataset.referenceUrl ?? '/scan/reference'}?${query}`, {
                    headers: { Accept: 'application/json' },
                });
                const payload = await response.json();
                if (generation !== this.referenceGeneration) return;
                if (!response.ok) {
                    const message = response.status === 429 ? root?.dataset.referenceRateLimit
                        : response.status === 401 ? root?.dataset.referenceSession : payload.message;
                    throw new Error(message || root?.dataset.referenceFailure || 'Referensi iNaturalist gagal diambil.');
                }
                if (!['matched', 'ambiguous', 'unavailable'].includes(payload.data?.match_status)) {
                    throw new Error(root?.dataset.referenceFailure || 'Respons referensi tidak valid.');
                }
                Object.assign(candidate, payload.data);
                this.applyReference(candidate);
                if (this.referenceStatus === 'ready') {
                    this.openFishDetail(this.reference.taxon_id);
                } else {
                    this.closePendingDetailTab();
                }
            } catch (error) {
                if (generation !== this.referenceGeneration) return;
                this.closePendingDetailTab();
                this.referenceStatus = 'unavailable';
                this.resultStatus = 'partial';
                this.referenceError = error instanceof TypeError
                    ? (root?.dataset.referenceNetwork || 'Koneksi ke server gagal. Periksa koneksi lalu coba lagi.')
                    : error instanceof SyntaxError
                        ? (root?.dataset.referenceFailure || 'Respons referensi tidak valid. Silakan coba lagi.')
                        : (error instanceof Error ? error.message : root?.dataset.referenceFailure);
            }
        },

        applyReference(candidate) {
            this.referenceOptions = candidate.reference_options ?? [];
            this.reference = candidate.match_status === 'matched' ? candidate.reference : null;
            this.referenceError = candidate.reference_error ?? '';
            this.referenceStatus = this.reference ? 'ready' : (candidate.match_status === 'ambiguous' ? 'ambiguous' : 'unavailable');
            this.resultStatus = this.reference ? 'success' : (this.referenceStatus === 'ambiguous' ? 'ambiguous' : 'partial');
        },

        selectReference(taxonId) {
            const option = this.referenceOptions.find((reference) => reference.taxon_id === taxonId);
            if (!option || this.selectedCandidateIndex === null) return;
            this.prepareDetailTab();
            this.reference = option;
            this.referenceStatus = 'ready';
            this.referenceImageFailed = false;
            this.resultStatus = 'success';
            this.openFishDetail(taxonId);
        },

        openFishDetail(taxonId) {
            if (!Number.isSafeInteger(taxonId) || taxonId < 1) return;
            const root = this.$refs.fileInput.closest('[data-scan]');
            const template = root?.dataset.fishDetailUrl ?? '/fish/taxa/__TAXON_ID__';
            if (pendingDetailTab && !pendingDetailTab.closed) {
                pendingDetailTab.location.replace(template.replace('__TAXON_ID__', String(taxonId)));
                pendingDetailTab = null;
                this.detailTabStatus = 'opened';
            } else {
                pendingDetailTab = null;
                this.detailTabStatus = 'blocked';
            }
        },

        prepareDetailTab() {
            this.closePendingDetailTab();
            const root = this.$refs.fileInput.closest('[data-scan]');
            pendingDetailTab = window.open(root?.dataset.detailLoadingUrl ?? '/scan/detail-loading', '_blank');
            if (pendingDetailTab) {
                pendingDetailTab.opener = null;
                this.detailTabStatus = 'opening';
            } else {
                this.detailTabStatus = 'blocked';
            }
        },

        closePendingDetailTab() {
            if (pendingDetailTab && !pendingDetailTab.closed) pendingDetailTab.close();
            pendingDetailTab = null;
        },

        chooseAnotherImage() {
            this.resetSelection();
            this.resetResult();

            const input = this.$refs.fileInput;

            if (!input) {
                return;
            }

            input.value = '';
            input.removeAttribute('capture');
            input.click();
        },

        async processImage() {
            // Mencegah double click
            if (!this.hasFile || this.processing) {
                return;
            }

            const input = this.$refs.fileInput;
            const file = input?.files?.[0];

            if (!file) {
                return;
            }

            const root = input.closest('[data-scan]');
            const csrfToken = document.querySelector(
                'meta[name="csrf-token"]'
            )?.content;

            this.resetResult();
            this.processing = true;
            this.resultStatus = 'loading';

            try {
                const formData = new FormData();
                formData.append('fish_image', file);

                const response = await fetch('/scan/upload', {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        ...(csrfToken
                            ? { 'X-CSRF-TOKEN': csrfToken }
                            : {}),
                    },
                    body: formData,
                });

                const payload = await response.json();

                if (!response.ok || payload.success === false) {
                    throw new Error(
                        payload.message ||
                        root?.dataset.errorRequest ||
                        ''
                    );
                }

                const candidates = payload.data?.candidates;

                // Reject responses that do not contain the identification contract.
                if (!Array.isArray(candidates)) {
                    this.resultStatus = 'failure';
                    this.resultError =
                        root?.dataset.errorResult ?? '';
                    return;
                }

                this.candidates = candidates;

                // The model could not identify a fish.
                if (candidates.length === 0) {
                    this.resultStatus = 'unknown';
                    return;
                }

                this.resultStatus = 'ambiguous';
            } catch (error) {
                this.resultStatus = 'failure';
                this.resultError =
                    error instanceof Error && error.message
                        ? error.message
                        : root?.dataset.errorRequest ?? '';
            } finally {
                this.processing = false;
            }
        },

        clearPreviewUrl() {
            if (!this.previewUrl) {
                return;
            }

            URL.revokeObjectURL(this.previewUrl);
            this.previewUrl = null;
        },

        destroy() {
            this.closePendingDetailTab();
            this.referenceGeneration += 1;
            this.selectionGeneration += 1;
            this.clearPreviewUrl();
        },
    };
}
