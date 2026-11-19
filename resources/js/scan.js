const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
const MAX_IMAGE_SIZE = 5 * 1024 * 1024;

export function scanUpload() {
    let pendingDetailTab = null;
    let cameraStream = null;

    return {
        previewUrl: null,
        selectedFileName: '',
        selectedFile: null,
        hasFile: false,
        operationId: null,
        historyId: null,
        historyUrl: null,
        errorMessage: '',
        processing: false,
        cameraStatus: 'idle',
        cameraNotice: '',
        cameraError: '',
        cameraGeneration: 0,

        selectionGeneration: 0,
        uploadGeneration: 0,

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

        async openCamera() {
            if (this.processing || this.cameraStatus !== 'idle') {
                return;
            }

            const messages = this.$refs.fileInput.closest('[data-scan]').dataset;
            this.cameraError = '';
            this.errorMessage = '';

            if (!navigator.mediaDevices?.getUserMedia) {
                const input = this.$refs.cameraInput;
                if (navigator.maxTouchPoints > 0 && input && 'capture' in input) {
                    this.cameraNotice = messages.cameraNative;
                    input.value = '';
                    input.click();
                } else {
                    this.cameraError = window.isSecureContext
                        ? messages.cameraUnsupported
                        : messages.cameraInsecure;
                }
                return;
            }

            const generation = ++this.cameraGeneration;
            this.cameraStatus = 'requesting';
            this.cameraNotice = messages.cameraPermission;

            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: { ideal: 'environment' } },
                    audio: false,
                });
                if (generation !== this.cameraGeneration) {
                    stream.getTracks().forEach((track) => track.stop());
                    return;
                }

                cameraStream = stream;
                const video = this.$refs.cameraVideo;
                video.srcObject = stream;
                await video.play();
                if (generation !== this.cameraGeneration) {
                    return;
                }
                this.cameraStatus = 'ready';
                this.cameraNotice = messages.cameraReady;
            } catch (error) {
                if (generation !== this.cameraGeneration) {
                    return;
                }
                this.stopCamera();
                const errors = {
                    NotAllowedError: messages.cameraDenied,
                    SecurityError: messages.cameraDenied,
                    NotFoundError: messages.cameraMissing,
                    NotReadableError: messages.cameraBusy,
                    AbortError: messages.cameraBusy,
                };
                this.cameraError = errors[error.name] || messages.cameraFailed;
            }
        },

        stopCamera() {
            this.cameraGeneration += 1;
            cameraStream?.getTracks().forEach((track) => track.stop());
            cameraStream = null;
            const video = this.$refs?.cameraVideo;
            if (video) {
                video.srcObject = null;
            }
            this.cameraStatus = 'idle';
            this.cameraNotice = '';
        },

        async capturePhoto() {
            if (this.cameraStatus !== 'ready') {
                return;
            }
            const video = this.$refs.cameraVideo;
            const messages = this.$refs.fileInput.closest('[data-scan]').dataset;
            if (!video.videoWidth || !video.videoHeight) {
                this.cameraError = messages.cameraFailed;
                return;
            }

            this.cameraStatus = 'capturing';
            this.cameraError = '';
            const generation = this.cameraGeneration;
            try {
                const canvas = document.createElement('canvas');
                const scale = Math.min(1, 1600 / Math.max(video.videoWidth, video.videoHeight));
                canvas.width = Math.max(1, Math.round(video.videoWidth * scale));
                canvas.height = Math.max(1, Math.round(video.videoHeight * scale));
                canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
                const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.9));
                if (generation !== this.cameraGeneration) {
                    return;
                }
                if (!blob) {
                    throw new Error('Camera frame could not be encoded');
                }
                const photo = new File([blob], `fish-${Date.now()}.jpg`, { type: 'image/jpeg' });
                this.stopCamera();
                this.$refs.fileInput.value = '';
                await this.handleFile({ target: this.$refs.fileInput }, photo);
            } catch {
                if (generation === this.cameraGeneration) {
                    this.cameraStatus = 'ready';
                    this.cameraError = messages.cameraFailed;
                }
            }
        },

        openUpload() {
            this.stopCamera();
            this.cameraError = '';
            const input = this.$refs.fileInput;

            input.value = '';
            input.click();
        },

        async handleFile(event, capturedFile = null) {
            const input = event.target;
            const file = capturedFile ?? input.files?.[0];
            if (!file) {
                return;
            }
            this.stopCamera();
            this.cameraError = '';
            // Mencegah hasil decode file lama menimpa file terbaru.
            const generation = ++this.selectionGeneration;

            this.resetResult();

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

                this.rejectFile(
                    input,
                    root?.dataset.errorImage ?? ''
                );

                return;
            }

            this.previewUrl = imageUrl;
            this.selectedFileName = file.name;
            this.selectedFile = file;
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
            this.operationId = null;
            this.clearPreviewUrl();

            this.selectedFileName = '';
            this.selectedFile = null;
            this.hasFile = false;
        },

        resetResult() {
            this.historyId = null;
            this.historyUrl = null;
            // Membatalkan response upload lama yang masih berjalan.
            this.uploadGeneration += 1;
            this.processing = false;

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

            if (
                this.referenceStatus === 'loading' &&
                this.selectedCandidateIndex === index
            ) {
                return;
            }

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
                const query = new URLSearchParams({
                    scientific_name: candidate.scientific_name,
                });

                const response = await fetch(
                    this.historyId
                        ? `/scan/history/${this.historyId}/reference`
                        : `${root?.dataset.referenceUrl ?? '/scan/reference'}?${query}`,
                    {
                        ...(this.historyId ? {
                            method: 'POST',
                            body: JSON.stringify({candidate_index: index}),
                        } : {}),
                        headers: {
                            Accept: 'application/json',
                            ...(this.historyId ? {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                            } : {}),
                        },
                    }
                );

                const payload = await response.json();

                // Response kandidat lama tidak boleh menimpa kandidat terbaru.
                if (generation !== this.referenceGeneration) {
                    return;
                }

                if (!response.ok) {
                    const message =
                        response.status === 429
                            ? root?.dataset.referenceRateLimit
                            : response.status === 401
                                ? root?.dataset.referenceSession
                                : payload.message;

                    throw new Error(
                        message ||
                        root?.dataset.referenceFailure ||
                        'Referensi iNaturalist gagal diambil.'
                    );
                }

                if (
                    ![
                        'matched',
                        'ambiguous',
                        'unavailable',
                    ].includes(payload.data?.match_status)
                ) {
                    throw new Error(
                        root?.dataset.referenceFailure ||
                        'Respons referensi tidak valid.'
                    );
                }

                Object.assign(candidate, payload.data);

                this.applyReference(candidate);

                if (this.referenceStatus === 'ready') {
                    this.openFishDetail(
                        this.reference.taxon_id
                    );
                } else {
                    this.closePendingDetailTab();
                }
            } catch (error) {
                if (generation !== this.referenceGeneration) {
                    return;
                }

                this.closePendingDetailTab();

                this.referenceStatus = 'unavailable';
                this.resultStatus = 'partial';

                this.referenceError =
                    error instanceof TypeError
                        ? (
                            root?.dataset.referenceNetwork ||
                            'Koneksi ke server gagal. Periksa koneksi lalu coba lagi.'
                        )
                        : error instanceof SyntaxError
                            ? (
                                root?.dataset.referenceFailure ||
                                'Respons referensi tidak valid. Silakan coba lagi.'
                            )
                            : (
                                error instanceof Error
                                    ? error.message
                                    : root?.dataset.referenceFailure
                            );
            }
        },

        applyReference(candidate) {
            this.referenceOptions =
                candidate.reference_options ?? [];

            this.reference =
                candidate.match_status === 'matched'
                    ? candidate.reference
                    : null;

            this.referenceError =
                candidate.reference_error ?? '';

            this.referenceStatus =
                this.reference
                    ? 'ready'
                    : candidate.match_status === 'ambiguous'
                        ? 'ambiguous'
                        : 'unavailable';

            this.resultStatus =
                this.reference
                    ? 'success'
                    : this.referenceStatus === 'ambiguous'
                        ? 'ambiguous'
                        : 'partial';
        },

        selectReference(taxonId) {
            const option = this.referenceOptions.find(
                (reference) =>
                    reference.taxon_id === taxonId
            );

            if (
                !option ||
                this.selectedCandidateIndex === null
            ) {
                return;
            }

            this.prepareDetailTab();

            this.reference = option;
            this.referenceStatus = 'ready';
            this.referenceImageFailed = false;
            this.resultStatus = 'success';

            this.openFishDetail(taxonId);
        },

        openFishDetail(taxonId) {
            if (
                !Number.isSafeInteger(taxonId) ||
                taxonId < 1
            ) {
                return;
            }

            const root =
                this.$refs.fileInput.closest('[data-scan]');

            const template =
                root?.dataset.fishDetailUrl ??
                '/fish/taxa/__TAXON_ID__';

            if (
                pendingDetailTab &&
                !pendingDetailTab.closed
            ) {
                pendingDetailTab.location.replace(
                    template.replace(
                        '__TAXON_ID__',
                        String(taxonId)
                    )
                );

                pendingDetailTab = null;
                this.detailTabStatus = 'opened';
            } else {
                pendingDetailTab = null;
                this.detailTabStatus = 'blocked';
            }
        },

        prepareDetailTab() {
            this.closePendingDetailTab();

            const root =
                this.$refs.fileInput.closest('[data-scan]');

            pendingDetailTab = window.open(
                root?.dataset.detailLoadingUrl ??
                    '/scan/detail-loading',
                '_blank'
            );

            if (pendingDetailTab) {
                pendingDetailTab.opener = null;
                this.detailTabStatus = 'opening';
            } else {
                this.detailTabStatus = 'blocked';
            }
        },

        closePendingDetailTab() {
            if (
                pendingDetailTab &&
                !pendingDetailTab.closed
            ) {
                pendingDetailTab.close();
            }

            pendingDetailTab = null;
        },

        chooseAnotherImage() {
            this.stopCamera();
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
            // Mencegah double click.
            if (!this.hasFile || this.processing || this.cameraStatus !== 'idle') {
                return;
            }

            const input = this.$refs.fileInput;
            const file = this.selectedFile ?? input?.files?.[0];

            if (!file) {
                return;
            }

            const root = input.closest('[data-scan]');

            const csrfToken = document.querySelector(
                'meta[name="csrf-token"]'
            )?.content;

            this.resetResult();

            // Setiap request upload mempunyai generation sendiri.
            const generation = ++this.uploadGeneration;

            this.processing = true;
            this.resultStatus = 'loading';

            try {
                const formData = new FormData();

                formData.append(
                    'fish_image',
                    file
                );
                this.operationId ??= this.createOperationId();
                formData.append('operation_id', this.operationId);

                const response = await fetch(
                    '/scan/upload',
                    {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                            ...(csrfToken
                                ? {
                                    'X-CSRF-TOKEN':
                                        csrfToken,
                                }
                                : {}),
                        },
                        body: formData,
                    }
                );

                const payload =
                    await response.json();

                // Response upload lama tidak boleh mengubah state terbaru.
                if (
                    generation !==
                    this.uploadGeneration
                ) {
                    return;
                }

                if (
                    !response.ok ||
                    payload.success === false
                ) {
                    throw new Error(
                        payload.message ||
                        root?.dataset.errorRequest ||
                        ''
                    );
                }

                const candidates =
                    payload.data?.candidates;

                if (!Array.isArray(candidates)) {
                    this.resultStatus = 'failure';

                    this.resultError =
                        root?.dataset.errorResult ?? '';

                    return;
                }

                this.candidates = candidates;
                this.historyId = payload.data?.history_id ?? null;
                this.historyUrl = payload.data?.history_url ?? null;

                if (candidates.length === 0) {
                    this.resultStatus = 'unknown';
                    return;
                }

                this.resultStatus = 'ambiguous';
            } catch (error) {
                // Error dari request lama juga harus diabaikan.
                if (
                    generation !==
                    this.uploadGeneration
                ) {
                    return;
                }

                this.resultStatus = 'failure';

                this.resultError =
                    error instanceof Error &&
                    error.message
                        ? error.message
                        : root?.dataset.errorRequest ?? '';
            } finally {
                // Request lama tidak boleh mengubah loading request terbaru.
                if (
                    generation ===
                    this.uploadGeneration
                ) {
                    this.processing = false;
                }
            }
        },

        clearPreviewUrl() {
            if (!this.previewUrl) {
                return;
            }

            URL.revokeObjectURL(
                this.previewUrl
            );

            this.previewUrl = null;
        },

        createOperationId() {
            if (typeof crypto.randomUUID === 'function') {
                return crypto.randomUUID();
            }

            const bytes = crypto.getRandomValues(new Uint8Array(16));
            bytes[6] = (bytes[6] & 0x0f) | 0x40;
            bytes[8] = (bytes[8] & 0x3f) | 0x80;
            const hex = Array.from(bytes, (byte) => byte.toString(16).padStart(2, '0')).join('');
            return `${hex.slice(0, 8)}-${hex.slice(8, 12)}-${hex.slice(12, 16)}-${hex.slice(16, 20)}-${hex.slice(20)}`;
        },

        destroy() {
            this.stopCamera();
            this.closePendingDetailTab();

            // Membuat seluruh proses async yang masih berjalan menjadi stale.
            this.uploadGeneration += 1;
            this.referenceGeneration += 1;
            this.selectionGeneration += 1;

            this.processing = false;

            this.clearPreviewUrl();
        },
    };
}
