export function initArticleForm() {
    const root = document.querySelector('[data-article-form]');

    if (!root) {
        return;
    }

    const form = root.querySelector('[data-article-form-element]');
    const imageInput = root.querySelector('[data-article-image-input]');
    const preview = root.querySelector('[data-article-image-preview]');
    const previewImage = root.querySelector('[data-article-image-preview-image]');
    const message = root.querySelector('[data-article-form-message]');
    const submitButton = root.querySelector('[data-article-submit]');
    const submitLabel = root.querySelector('[data-submit-label]');
    const submitLoading = root.querySelector('[data-submit-loading]');

    const mode = root.dataset.mode;
    const articleId = root.dataset.articleId ?? null;

    let submitting = false;
    let articleReady = mode !== 'edit';
    let previewUrl = null;

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    function clearMessage() {
        message.hidden = true;
        message.textContent = '';
        message.removeAttribute('data-state');
        message.removeAttribute('role');
    }

    function showMessage(state, text) {
        message.dataset.state = state;
        message.textContent = text;
        message.hidden = false;

        if (state === 'error') {
            message.setAttribute('role', 'alert');
        } else {
            message.removeAttribute('role');
        }
    }

    function clearFieldError(name) {
        const field = form.elements.namedItem(name);
        const error = root.querySelector(`[data-error-for="${name}"]`);

        if (field instanceof HTMLElement) {
            field.removeAttribute('aria-invalid');
            field.removeAttribute('aria-describedby');
        }

        if (error) {
            error.hidden = true;
            error.textContent = '';
            error.removeAttribute('id');
        }
    }

    function clearFieldErrors() {
        ['judul', 'kategori', 'isi', 'gambar'].forEach(clearFieldError);
    }

    function showFieldError(name, text) {
        const field = form.elements.namedItem(name);
        const error = root.querySelector(`[data-error-for="${name}"]`);

        if (!(field instanceof HTMLElement) || !error) {
            return;
        }

        const errorId = `article-${name}-error`;

        error.id = errorId;
        error.textContent = text;
        error.hidden = false;

        field.setAttribute('aria-invalid', 'true');
        field.setAttribute('aria-describedby', errorId);
    }

    function focusFirstInvalidField() {
        root.querySelector('[aria-invalid="true"]')?.focus();
    }

    function setSubmitting(value) {
        submitting = value;
        submitButton.disabled = value || !articleReady;
        submitLabel.hidden = value;
        submitLoading.hidden = !value;
    }

    function clearPreviewUrl() {
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
            previewUrl = null;
        }
    }

    function hidePreview() {
        clearPreviewUrl();
        preview.hidden = true;
        previewImage.removeAttribute('src');
    }

    function showStoredImage(path) {
        if (!path) {
            return;
        }

        previewImage.src = `/storage/${path.replace(/^\/+/, '')}`;
        preview.hidden = false;
    }

    function handleImageChange() {
        clearFieldError('gambar');
        clearPreviewUrl();

        const file = imageInput.files?.[0];

        if (!file) {
            hidePreview();
            return;
        }

        if (!['image/jpeg', 'image/png'].includes(file.type)) {
            imageInput.value = '';
            showFieldError('gambar', root.dataset.errorImageFormat);
            hidePreview();
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            imageInput.value = '';
            showFieldError('gambar', root.dataset.errorImageMax);
            hidePreview();
            return;
        }

        previewUrl = URL.createObjectURL(file);
        previewImage.src = previewUrl;
        preview.hidden = false;
    }

    previewImage.addEventListener('error', () => {
        if (!previewImage.src.startsWith('blob:')) {
            return;
        }

        imageInput.value = '';
        hidePreview();

        showFieldError(
            'gambar',
            root.dataset.errorImageInvalid,
        );
    });

    imageInput.addEventListener('change', handleImageChange);

    async function loadArticle() {
        if (mode !== 'edit' || !articleId) {
            return;
        }

        const fields = ['judul', 'kategori', 'isi', 'gambar']
            .map((name) => form.elements.namedItem(name));
        fields.forEach((field) => { field.disabled = true; });
        submitButton.disabled = true;
        form.setAttribute('aria-busy', 'true');

        try {
            const response = await fetch(`/artikel/${articleId}`, {
                headers: {
                    Accept: 'application/json',
                },
                credentials: 'same-origin',
            });

            const payload = await response.json();

            if (!response.ok) {
                throw new Error(
                    payload.message || root.dataset.errorLoad,
                );
            }

            const article = payload.data;

            form.elements.judul.value = article.judul ?? '';
            form.elements.kategori.value = article.kategori ?? '';
            form.elements.isi.value = article.isi ?? '';

            showStoredImage(article.gambar);
            articleReady = true;
            fields.forEach((field) => { field.disabled = false; });
            submitButton.disabled = false;
        } catch (error) {
            showMessage(
                'error',
                error instanceof Error
                    ? error.message
                    : root.dataset.errorLoad,
            );
        } finally {
            form.removeAttribute('aria-busy');
        }
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (submitting || !articleReady) {
            return;
        }

        clearMessage();
        clearFieldErrors();

        const formData = new FormData(form);

        let endpoint = '/artikel';

        if (mode === 'edit' && articleId) {
            endpoint = `/artikel/${articleId}`;
            formData.append('_method', 'PUT');
        }

        setSubmitting(true);

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    ...(csrfToken
                        ? { 'X-CSRF-TOKEN': csrfToken }
                        : {}),
                },
                credentials: 'same-origin',
                body: formData,
            });

            const payload = await response.json();

            if (response.status === 422) {
                Object.entries(payload.errors ?? {}).forEach(
                    ([name, errors]) => {
                        const firstError = Array.isArray(errors)
                            ? errors[0]
                            : errors;

                        showFieldError(name, firstError);
                    },
                );

                focusFirstInvalidField();
                return;
            }

            if (!response.ok) {
                throw new Error(
                    payload.message || root.dataset.errorSubmit,
                );
            }

            showMessage('success', payload.message);

            if (mode === 'create') {
                form.reset();
                hidePreview();
            }
        } catch (error) {
            showMessage(
                'error',
                error instanceof Error
                    ? error.message
                    : root.dataset.errorSubmit,
            );
        } finally {
            setSubmitting(false);
        }
    });

    loadArticle();

    window.addEventListener('beforeunload', clearPreviewUrl);
}
