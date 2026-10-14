import { initFishPhotoViewer } from './fish-photo-viewer';

export function initFishGallery(root, onSelect) {
    const gallery = root.querySelector('[data-fish-gallery]');
    if (!gallery) return { addPhotos() {} };
    const config = JSON.parse(gallery.dataset.fishGallery);
    const photos = [...config.photos];
    const thumbnails = gallery.querySelector('[data-gallery-thumbnails]');
    const more = gallery.querySelector('[data-gallery-more]');
    const previous = gallery.querySelector('[data-gallery-prev]');
    const next = gallery.querySelector('[data-gallery-next]');
    let selected = 0;
    let cursor = null;
    let exhausted = false;
    let loading = false;
    let failed = false;
    const viewer = initFishPhotoViewer(gallery, () => ({ photos, selected }), select);

    function render() {
        gallery.querySelector('[data-gallery-count]').textContent = photos.length ? `${selected + 1} / ${photos.length}` : '';
        gallery.querySelector('[data-gallery-navigation]').hidden = photos.length < 2;
        previous.disabled = selected === 0;
        next.disabled = selected >= photos.length - 1;
        thumbnails.querySelectorAll('[data-gallery-index]').forEach((button) => {
            button.setAttribute('aria-pressed', String(Number(button.dataset.galleryIndex) === selected));
        });
        more.hidden = exhausted;
        more.disabled = loading;
        more.textContent = loading ? config.loadingLabel : failed ? config.retryLabel : config.loadLabel;
        gallery.querySelector('[data-gallery-loading]').hidden = !loading;
        gallery.querySelector('[data-gallery-error]').hidden = !failed;
        gallery.querySelector('[data-gallery-end]').hidden = !exhausted;
        gallery.setAttribute('aria-busy', String(loading));
        viewer.update();
    }

    function select(index) {
        if (index < 0 || index >= photos.length) return;
        selected = index;
        onSelect(photos[selected]);
        render();
        const thumbnail = thumbnails.querySelector(`[data-gallery-index="${selected}"]`);
        if (thumbnail && (thumbnail.offsetLeft < thumbnails.scrollLeft || thumbnail.offsetLeft + thumbnail.offsetWidth > thumbnails.scrollLeft + thumbnails.clientWidth)) {
            thumbnails.scrollTo({ left: Math.max(0, thumbnail.offsetLeft - (thumbnails.clientWidth - thumbnail.offsetWidth) / 2), behavior: 'instant' });
        }
    }

    function addPhotos(incoming) {
        const hadPhotos = photos.length > 0;
        incoming.forEach((photo) => {
            if (photos.some((existing) => existing.source_url === photo.source_url)) return;
            const index = photos.length;
            photos.push(photo);
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'fish-gallery-thumbnail';
            button.dataset.galleryIndex = index;
            button.setAttribute('aria-label', `${config.photoLabel} ${index + 1}: ${config.species}`);
            const image = document.createElement('img');
            image.src = photo.thumbnail_url || photo.url;
            image.alt = '';
            image.width = 80;
            image.height = 64;
            image.loading = 'lazy';
            image.decoding = 'async';
            image.referrerPolicy = 'no-referrer';
            const marker = document.createElement('span');
            marker.className = 'fish-gallery-selected';
            marker.setAttribute('aria-hidden', 'true');
            marker.textContent = '✓';
            button.append(image, marker);
            thumbnails.append(button);
        });
        if (!hadPhotos && photos.length) select(0);
        render();
    }

    thumbnails.addEventListener('click', (event) => {
        const button = event.target.closest('[data-gallery-index]');
        if (button) select(Number(button.dataset.galleryIndex));
    });
    thumbnails.addEventListener('error', (event) => {
        if (event.target.tagName === 'IMG') event.target.hidden = true;
    }, true);
    previous.addEventListener('click', () => select(selected - 1));
    next.addEventListener('click', () => select(selected + 1));
    gallery.addEventListener('keydown', (event) => {
        if (event.defaultPrevented || event.target.closest('[data-photo-viewer]')) return;
        if (!['ArrowLeft', 'ArrowRight'].includes(event.key) || !photos.length) return;
        event.preventDefault();
        select(selected + (event.key === 'ArrowRight' ? 1 : -1));
        const current = thumbnails.querySelector(`[data-gallery-index="${selected}"]`);
        current?.focus({ preventScroll: true });
    });
    more.addEventListener('click', async () => {
        if (loading || exhausted) return;
        loading = true;
        failed = false;
        render();
        try {
            const url = new URL(config.endpoint, window.location.origin);
            if (cursor !== null) url.searchParams.set('before', cursor);
            const response = await fetch(url, { headers: { Accept: 'application/json' }, signal: AbortSignal.timeout(10000) });
            if (!response.ok) throw new Error('Photo gallery unavailable');
            const result = await response.json();
            addPhotos(result.photos);
            cursor = result.next_cursor;
            exhausted = cursor === null;
        } catch {
            failed = true;
        } finally {
            loading = false;
            render();
            if (exhausted) {
                const focusTarget = thumbnails.querySelector(`[data-gallery-index="${selected}"]`) || gallery.querySelector('[data-detail-retry]');
                focusTarget?.focus({ preventScroll: true });
            }
        }
    });
    render();
    return { addPhotos };
}
