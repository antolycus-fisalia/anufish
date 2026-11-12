
function initializeArticleModeration() {
    const panel = document.querySelector('[data-moderation-panel]');

    if (!panel) return;

    const forms = [...panel.querySelectorAll('[data-moderation-form]')];
    const buttons = forms.map((form) => form.querySelector('button'));
    const feedback = panel.querySelector('[data-moderation-feedback]');
    const refreshLink = panel.querySelector('[data-moderation-refresh]');

    let submitting = false;

    function showError(message) {
        feedback.textContent = message;
        feedback.classList.remove('hidden');
        feedback.setAttribute('role', 'alert');
    }

    for (const form of forms) {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (submitting) return;

            const confirmation = form.dataset.confirm;

            if (confirmation && !window.confirm(confirmation)) {
                return;
            }

            submitting = true;
            buttons.forEach((button) => {
                button.disabled = true;
            });

            feedback.classList.add('hidden');
            refreshLink.classList.add('hidden');

            let succeeded = false;

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

                const result = await response.json().catch(() => ({}));

                if (!response.ok) {
                    showError(result.message || panel.dataset.error);

                    if (response.status === 409) {
                        refreshLink.classList.remove('hidden');
                    }

                    return;
                }

                succeeded = true;

                if (form.dataset.operation === 'delete') {
                    window.location.assign(form.dataset.redirect);
                } else {
                    window.location.reload();
                }
            } catch {
                showError(panel.dataset.error);
            } finally {
                if (!succeeded) {
                    submitting = false;
                    buttons.forEach((button) => {
                        button.disabled = false;
                    });
                }
            }
        });
    }
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
