const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png'];
const MAX_IMAGE_SIZE = 5 * 1024 * 1024;

export function scanUpload() {
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
            this.resultStatus = 'idle';
            this.candidates = [];
            this.selectedCandidateIndex = null;
            this.reference = null;
            this.referenceStatus = 'idle';
            this.referenceImageFailed = false;
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

        selectCandidate(index) {
            if (
                !Number.isInteger(index) ||
                index < 0 ||
                index >= this.candidates.length
            ) {
                return;
            }

            this.selectedCandidateIndex = index;
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

                // Lebih dari satu kandidat harus dipilih user
                if (candidates.length > 1) {
                    this.resultStatus = 'ambiguous';
                    return;
                }

                // One AI candidate was identified.
                // Detail iNaturalist masih menunggu FSH-15
                this.selectedCandidateIndex = 0;
                this.resultStatus = 'partial';
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
            this.selectionGeneration += 1;
            this.clearPreviewUrl();
        },
    };
}
