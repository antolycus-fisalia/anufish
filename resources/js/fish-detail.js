import { initFishGallery } from './fish-gallery';

export function initFishDetails() {
    document.querySelectorAll('[data-fish-detail]').forEach((root) => {
        const state = { ...JSON.parse(root.dataset.fishDetail), loading: false, error: false, failed: false, photoError: false, descriptionError: false };
        const photo = root.querySelector('[data-detail-image]');
        state.imageLoading = Boolean(state.image) && !photo.complete;
        const gallery = initFishGallery(root, (image) => {
            state.image = image;
            state.failed = false;
            state.photoError = false;
            render();
        });
        function render() {
            if (state.image && photo.getAttribute('src') !== state.image.url) {
                state.imageLoading = true;
                photo.src = state.image.url;
            }
            const visibility = {
                photo: Boolean(state.image) && !state.failed,
                placeholder: !state.image || state.failed,
                loading: state.loading, idle: !state.loading && !state.photoError && !state.failed,
                'image-loading': state.imageLoading && !state.failed,
                'photo-error': !state.loading && (state.photoError || state.failed),
                retry: state.error || state.failed,
                description: Boolean(state.description),
                'description-loading': !state.description && state.loading,
                'description-missing': !state.description && !state.loading && !state.descriptionError,
                'description-source': Boolean(state.description),
                'english-fallback': Boolean(state.description) && state.descriptionLanguage === 'en',
                error: state.error,
            };
            root.querySelectorAll('[data-detail-state]').forEach((element) => { element.hidden = !visibility[element.dataset.detailState]; });
            if (state.image) {
                root.querySelector('[data-detail-credit]').textContent = state.image.credit;
                root.querySelector('[data-detail-source]').href = state.image.source_url;
                const license = root.querySelector('[data-detail-license]');
                license.href = state.image.license.url;
                license.textContent = state.image.license.label;
            }
            const description = root.querySelector('[data-detail-description]');
            description.textContent = state.description;
            description.lang = state.descriptionLanguage;
        }
        async function loadExtras() {
            if (state.loading) return;
            state.loading = true;
            state.error = false;
            state.photoError = false;
            state.descriptionError = false;
            render();
            try {
                const response = await fetch(state.endpoint, { headers: { Accept: 'application/json' }, signal: AbortSignal.timeout(10000) });
                if (!response.ok) throw new Error('Detail enrichment unavailable');
                const data = await response.json();
                state.image ??= data.image;
                if (data.image) gallery.addPhotos([data.image]);
                state.description = data.description || state.description;
                state.descriptionLanguage = data.description_language;
                state.photoError = data.image_status !== 200 && data.image_status !== 404;
                state.descriptionError = data.description_status !== 200 && data.description_status !== 404;
                state.error = state.photoError || state.descriptionError;
                state.failed = false;
            } catch {
                state.error = true;
                state.photoError = !state.image;
                state.descriptionError = !state.description;
            }
            finally { state.loading = false; render(); }
        }
        photo.addEventListener('load', () => { state.imageLoading = false; state.failed = false; render(); });
        photo.addEventListener('error', () => { state.imageLoading = false; state.failed = true; render(); });
        if (photo.getAttribute('src') && photo.complete && !photo.naturalWidth) state.failed = true;
        root.querySelectorAll('[data-detail-retry]').forEach((button) => button.addEventListener('click', () => {
            if (state.failed && state.image) {
                state.failed = false;
                state.imageLoading = true;
                photo.src = state.image.url;
                render();
            } else loadExtras();
        }));
        render();
        if (state.needsExtras) loadExtras();
    });
}
