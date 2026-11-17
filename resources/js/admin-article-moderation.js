
function initializeArticleModeration() {
    const panel = document.querySelector('[data-moderation-panel]');

    if (!panel) return;

    const forms = [...panel.querySelectorAll('[data-moderation-form]')];
    const buttons = forms.map((form) => form.querySelector('button'));
    const feedback = panel.querySelector('[data-moderation-feedback]');
    const refreshLink = panel.querySelector('[data-moderation-refresh]');
    const listLink = panel.querySelector('[data-moderation-list]');
    const deleteForm = forms.find((form) => form.dataset.operation === 'delete');
    const deleteDialog = panel.querySelector('[data-moderation-delete-dialog]');

    let submitting = false;

    function showError(message) {
        feedback.textContent = message;
        feedback.classList.remove('hidden');
        feedback.setAttribute('role', 'alert');
    }

    async function submit(form) {
        if (submitting) return;

        submitting = true;
        buttons.forEach((button) => {
            button.disabled = true;
        });

        feedback.classList.add('hidden');
        refreshLink.classList.add('hidden');
        listLink.classList.add('hidden');

        let canRetry = true;

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new FormData(form),
            });

            if (response.status === 404) {
                canRetry = false;
                showError(panel.dataset.missing);
                listLink.classList.remove('hidden');
                return;
            }

            if (!response.ok) {
                const result = await response.json().catch(() => ({}));
                showError(result.message || panel.dataset.error);

                if (response.status === 409) {
                    refreshLink.classList.remove('hidden');
                }

                return;
            }

            canRetry = false;

            if (form === deleteForm) {
                window.location.assign(form.dataset.redirect);
            } else {
                window.location.reload();
            }
        } catch {
            showError(panel.dataset.error);
        } finally {
            if (canRetry) {
                submitting = false;
                buttons.forEach((button) => {
                    button.disabled = false;
                });
            }
        }
    }

    for (const form of forms) {
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            if (form !== deleteForm) submit(form);
        });
    }

    deleteForm.querySelector('button').addEventListener('click', () => {
        if (submitting) return;
        deleteDialog.returnValue = '';
        deleteDialog.showModal();
    });

    deleteDialog.addEventListener('close', () => {
        if (deleteDialog.returnValue === 'delete') submit(deleteForm);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener(
        'DOMContentLoaded',
        initializeArticleModeration,
        { once: true }
    );
} else {
    initializeArticleModeration();
}
