export function initArticleForm() {
    const root = document.querySelector('[data-article-form]');

    if (!root) {
        return;
    }

    const form = root.querySelector('[data-article-form-element]');
    const imageInput = root.querySelector('[data-article-image-input]');
    const clearImage = root.querySelector('[data-article-image-clear]');
    const preview = root.querySelector('[data-article-image-preview]');
    const previewImage = root.querySelector('[data-article-image-preview-image]');
    const message = root.querySelector('[data-article-form-message]');
    const messageText = root.querySelector('[data-article-form-message-text]');
    const successLink = root.querySelector('[data-article-form-success-link]');
    const submitButton = root.querySelector('[data-article-submit]');
    const submitLabel = root.querySelector('[data-submit-label]');
    const submitLoading = root.querySelector('[data-submit-loading]');

    const mode = root.dataset.mode;
    const imageLimit = Number(root.dataset.imageMaxBytes);

    let submitting = false;
    let articleReady = mode !== 'edit';
    let previewUrl = null;
    let storedImage = null;
    let imageError = null;

    function clearMessage() {
        message.hidden = true;
        messageText.textContent = '';
        message.removeAttribute('data-state');
        successLink.hidden = true;
    }

    function showMessage(state, text) {
        message.dataset.state = state;
        messageText.setAttribute('role', state === 'error' ? 'alert' : 'status');
        message.hidden = false;
        messageText.textContent = text;
        successLink.hidden = state !== 'success';
    }

    function clearFieldError(name) {
        const field = form.elements.namedItem(name);
        const error = root.querySelector(`[data-error-for="${name}"]`);

        if (field instanceof HTMLElement) {
            field.removeAttribute('aria-invalid');

            if (field === imageInput) {
                field.setAttribute('aria-describedby', 'article-image-help');
            } else {
                field.removeAttribute('aria-describedby');
            }
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
        field.setAttribute('aria-describedby', field === imageInput ? `${errorId} article-image-help` : errorId);
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

    async function handleImageChange() {
        clearFieldError('gambar');
        hidePreview();

        const file = imageInput.files?.[0];
        clearImage.hidden = !file;
        imageError = null;

        if (!file) {
            if (storedImage) {
                previewImage.src = `/storage/${storedImage}`;
                preview.hidden = false;
            }
            return;
        }

        if (!/\.(jpeg|jpg|png|webp)$/i.test(file.name)) {
            imageError = root.dataset.errorImageFormat;
        } else if (file.size > imageLimit) {
            imageError = root.dataset.errorImageMax;
        }

        if (imageError) {
            showFieldError('gambar', imageError);
            return;
        }

        const url = URL.createObjectURL(file);
        previewUrl = url;
        const image = new Image();
        image.src = url;

        try {
            await image.decode();
            if (previewUrl === url) {
                previewImage.src = url;
                preview.hidden = false;
            }
        } catch {
            if (previewUrl === url) {
                hidePreview();
                imageError = root.dataset.errorImageInvalid;
                showFieldError('gambar', imageError);
            }
        }
    }

    imageInput.addEventListener('change', handleImageChange);
    clearImage.addEventListener('click', () => {
        imageInput.value = '';
        handleImageChange();
        imageInput.focus();
    });

    async function loadArticle() {
        if (mode !== 'edit') {
            return;
        }

        const fields = ['judul', 'kategori', 'isi', 'gambar']
            .map((name) => form.elements.namedItem(name));
        fields.forEach((field) => { field.disabled = true; });
        submitButton.disabled = true;
        form.setAttribute('aria-busy', 'true');

        try {
            const response = await fetch(root.dataset.loadUrl, {
                headers: {
                    Accept: 'application/json',
                },
                credentials: 'same-origin',
            });

            const payload = await response.json().catch(() => null);

            if (!response.ok || !payload?.data) {
                showMessage('error', payload?.message || root.dataset.errorLoad);
                return;
            }

            const article = payload.data;

            form.elements.judul.value = article.judul ?? '';
            form.elements.kategori.value = article.kategori ?? '';
            form.elements.isi.value = article.isi ?? '';

            storedImage = article.gambar;
            handleImageChange();
            articleReady = true;
            fields.forEach((field) => { field.disabled = false; });
            submitButton.disabled = false;
        } catch {
            showMessage('error', root.dataset.errorLoad);
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

        if (imageError) {
            showFieldError('gambar', imageError);
            imageInput.focus();
            return;
        }

        setSubmitting(true);

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                },
                credentials: 'same-origin',
                body: new FormData(form),
            });

            if (response.status === 413) {
                showMessage('error', root.dataset.errorRequestSize);
                return;
            }

            const payload = await response.json().catch(() => null);

            if (response.status === 422 && payload?.errors) {
                Object.entries(payload.errors).forEach(
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

            if (!response.ok || !payload?.data) {
                showMessage('error', payload?.message || root.dataset.errorSubmit);
                return;
            }

            showMessage('success', mode === 'create' ? root.dataset.successCreate : payload.message);

            if (mode === 'create') {
                form.reset();
            } else {
                form.elements.isi.value = payload.data.isi;
                storedImage = payload.data.gambar;
                imageInput.value = '';
            }
            handleImageChange();
        } catch {
            showMessage('error', root.dataset.errorSubmit);
        } finally {
            setSubmitting(false);
        }
    });

    loadArticle();

    window.addEventListener('beforeunload', clearPreviewUrl);
}
