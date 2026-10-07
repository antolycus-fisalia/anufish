import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('profileForm', () => ({
    previewUrl: null,
    submitting: false,

    previewPhoto(event) {
        const file = event.target.files?.[0];

        if (!file) {
            this.clearPreview();
            return;
        }

        this.clearPreview();
        this.previewUrl = URL.createObjectURL(file);
    },

    clearPreview() {
        if (this.previewUrl) {
            URL.revokeObjectURL(this.previewUrl);
            this.previewUrl = null;
        }
    },

    startSubmitting() {
        this.submitting = true;
    },

    destroy() {
        this.clearPreview();
    },
}));

Alpine.start();