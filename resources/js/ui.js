function setFormState(scope, state) {
    scope.querySelectorAll('[data-form-state]').forEach((element) => { element.hidden = element.dataset.formState !== state; });
    scope.querySelectorAll('[data-submit-button]').forEach((button) => { button.disabled = state !== 'idle'; });
}

export function initNavigation() {
    document.querySelectorAll('[data-navigation-shell]').forEach((shell) => {
        const dialog = shell.querySelector('[data-navigation-dialog]');
        const opener = shell.querySelector('[data-navigation-open]');
        opener.addEventListener('click', () => dialog.showModal());
        shell.querySelector('[data-navigation-close]').addEventListener('click', () => dialog.close());
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog || event.target.closest('a')) dialog.close();
        });
        dialog.addEventListener('close', () => opener.focus());
        window.addEventListener('resize', () => { if (window.innerWidth >= 1024 && dialog.open) dialog.close(); });
    });
}

export function initPasswordFields() {
    document.querySelectorAll('[data-password-field]').forEach((field) => {
        const input = field.querySelector('input');
        const button = field.querySelector('[data-password-toggle]');
        button.addEventListener('click', () => {
            const visible = input.type === 'password';
            input.type = visible ? 'text' : 'password';
            button.setAttribute('aria-pressed', String(visible));
            button.setAttribute('aria-label', visible ? button.dataset.hideLabel : button.dataset.showLabel);
            button.querySelector('[data-password-eye]').hidden = visible;
            button.querySelector('[data-password-eye-off]').hidden = !visible;
        });
    });
}

export function initForms() {
    document.querySelectorAll('[data-submit-form]').forEach((form) => {
        const scope = form.closest('[data-search-scope]') ?? form;
        form.addEventListener('submit', () => setFormState(scope, form.dataset.submitForm));
        scope.querySelectorAll('[data-search-link]').forEach((link) => {
            link.addEventListener('click', (event) => {
                if (event.button === 0 && !event.ctrlKey && !event.metaKey && !event.shiftKey && !event.altKey) setFormState(scope, 'loading');
            });
        });
        window.addEventListener('pageshow', () => setFormState(scope, 'idle'));
    });
}

export function initProfile() {
    document.querySelectorAll('[data-profile-form]').forEach((form) => {
        let previewUrl;
        const input = form.querySelector('[name="profile_photo"]');
        const preview = form.querySelector('[data-profile-preview]');
        const original = form.querySelector('[data-profile-original]');
        const clearPreview = () => { if (previewUrl) URL.revokeObjectURL(previewUrl); previewUrl = null; };
        const updatePreview = () => {
            clearPreview();
            const file = input.files?.[0];
            preview.hidden = !file;
            original.hidden = Boolean(file);
            if (file) { previewUrl = URL.createObjectURL(file); preview.src = previewUrl; }
            else preview.removeAttribute('src');
        };
        input.addEventListener('change', updatePreview);
        window.addEventListener('pageshow', updatePreview);
        window.addEventListener('pagehide', clearPreview);
    });
}
