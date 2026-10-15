const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png'];
const MAX_IMAGE_SIZE = 5 * 1024 * 1024;

export function initScan() {
    document.querySelectorAll('[data-scan]').forEach((scope) => {
        const input = scope.querySelector('[data-scan-input]');
        const cameraButton = scope.querySelector('[data-scan-camera]');
        const uploadButton = scope.querySelector('[data-scan-upload]');
        const preview = scope.querySelector('[data-scan-preview]');
        const placeholder = scope.querySelector('[data-scan-placeholder]');
        const error = scope.querySelector('[data-scan-error]');
        const fileName = scope.querySelector('[data-scan-file-name]');
        const processButton = scope.querySelector('[data-scan-process]');
        const states = scope.querySelectorAll('[data-scan-state]');

        if (
            !input ||
            !cameraButton ||
            !uploadButton ||
            !preview ||
            !placeholder ||
            !error ||
            !fileName ||
            !processButton
        ) {
            return;
        }

        let previewUrl = null;
        let selectedFile = null;
        let processing = false;

        const clearPreviewUrl = () => {
            if (!previewUrl) {
                return;
            }

            URL.revokeObjectURL(previewUrl);
            previewUrl = null;
        };

        const setProcessState = (state) => {
            states.forEach((element) => {
                element.hidden = element.dataset.scanState !== state;
            });
        };

        const setError = (message = '') => {
            error.textContent = message;
            error.hidden = message === '';
        };

        const resetPreview = () => {
            clearPreviewUrl();

            preview.hidden = true;
            preview.removeAttribute('src');

            placeholder.hidden = false;

            fileName.hidden = true;
            fileName.textContent = '';

            selectedFile = null;
            processButton.disabled = true;
        };

        const showPreview = (file) => {
            clearPreviewUrl();

            previewUrl = URL.createObjectURL(file);
            preview.src = previewUrl;
            preview.hidden = false;

            placeholder.hidden = true;

            fileName.textContent = file.name;
            fileName.hidden = false;

            selectedFile = file;
            processButton.disabled = false;

            setError();
        };

        const validateFile = (file) => {
            if (!ALLOWED_IMAGE_TYPES.includes(file.type)) {
                return scope.dataset.errorFormat;
            }

            if (file.size > MAX_IMAGE_SIZE) {
                return scope.dataset.errorSize;
            }

            return null;
        };

        const handleFile = () => {
            const file = input.files?.[0];

            if (!file) {
                return;
            }

            const validationError = validateFile(file);

            if (validationError) {
                resetPreview();
                input.value = '';
                setError(validationError);
                return;
            }

            showPreview(file);
        };

        cameraButton.addEventListener('click', () => {
            input.setAttribute('capture', 'environment');
            input.click();
        });

        uploadButton.addEventListener('click', () => {
            input.removeAttribute('capture');
            input.click();
        });

        input.addEventListener('change', handleFile);

        processButton.addEventListener('click', () => {
            if (!selectedFile || processing) {
                return;
            }

            processing = true;
            processButton.disabled = true;
            cameraButton.disabled = true;
            uploadButton.disabled = true;

            setProcessState('loading');

            // Fixture sementara sampai endpoint identifikasi dari backend tersedia.
            window.setTimeout(() => {
                processing = false;

                processButton.disabled = false;
                cameraButton.disabled = false;
                uploadButton.disabled = false;

                setProcessState('idle');
            }, 1000);
        });

        window.addEventListener('pagehide', clearPreviewUrl);
    });
}
