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
            // Setiap pilihan file mempunyai generation sendiri.
            // Hasil async dari pilihan lama tidak boleh mengubah state terbaru.
            const generation = ++this.selectionGeneration;

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

            // User sudah memilih file lain ketika decode masih berjalan.
            if (generation !== this.selectionGeneration) {
                URL.revokeObjectURL(imageUrl);
                return;
            }

            if (!imageIsValid) {
                URL.revokeObjectURL(imageUrl);

                this.rejectFile(
                    input,
                    root?.dataset.errorImage ?? ''
                );

                return;
            }

            this.previewUrl = imageUrl;
            this.selectedFileName = file.name;
            this.hasFile = true;
            this.errorMessage = '';
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
                    resolve(
                        image.naturalWidth > 0 &&
                        image.naturalHeight > 0
                    );
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

            this.previewUrl = null;
            this.selectedFileName = '';
            this.hasFile = false;
        },

        processImage() {
            if (!this.hasFile || this.processing) {
                return;
            }

            this.processing = true;

            // Fixture sementara sampai endpoint identifikasi tersedia.
            window.setTimeout(() => {
                this.processing = false;
            }, 1000);
        },

        clearPreviewUrl() {
            if (!this.previewUrl) {
                return;
            }

            URL.revokeObjectURL(this.previewUrl);
            this.previewUrl = null;
        },

        destroy() {
            // Decode yang masih berjalan menjadi stale setelah component dihancurkan.
            this.selectionGeneration += 1;
            this.clearPreviewUrl();
        },
    };
}
