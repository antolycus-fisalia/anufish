const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png'];
const MAX_IMAGE_SIZE = 5 * 1024 * 1024;

export function scanUpload() {
    return {
        previewUrl: null,
        selectedFileName: '',
        hasFile: false,
        errorMessage: '',
        processing: false,

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

        handleFile(event) {
            const input = event.target;
            const file = input.files?.[0];

            if (!file) {
                return;
            }

            const root = input.closest('[data-scan]');
            const validationError = this.validateFile(file, root);

            if (validationError) {
                this.resetSelection();
                input.value = '';
                this.errorMessage = validationError;

                return;
            }

            this.clearPreviewUrl();

            this.previewUrl = URL.createObjectURL(file);
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

            // Fixture sementara sampai endpoint FSH-13 tersedia.
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
            this.clearPreviewUrl();
        },
    };
}
