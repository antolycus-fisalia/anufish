export function initFishPhotoViewer(gallery, getSelection, select) {
    const dialog = gallery.querySelector('[data-photo-viewer]');
    const opener = gallery.querySelector('[data-viewer-open]');
    const image = dialog.querySelector('[data-viewer-image]');
    const main = gallery.closest('main');
    const previous = dialog.querySelector('[data-viewer-prev]');
    const next = dialog.querySelector('[data-viewer-next]');
    const loadingMessage = dialog.querySelector('[data-viewer-loading]');
    const errorMessage = dialog.querySelector('[data-viewer-error]');
    let current = null;
    let loading = false;
    let failed = false;
    let mediumFallback = false;

    function renderImageState() {
        loadingMessage.hidden = !loading;
        errorMessage.hidden = !failed;
        image.hidden = loading || failed;
        dialog.querySelector('[data-viewer-stage]').setAttribute('aria-busy', String(loading));
    }

    function loadImage() {
        loading = true;
        failed = false;
        mediumFallback = false;
        renderImageState();
        image.src = current.large_url || current.url;
    }

    function update() {
        if (!dialog.open) return;
        const { photos, selected } = getSelection();
        const photo = photos[selected];
        if (!photo) return;
        previous.disabled = selected === 0;
        next.disabled = selected === photos.length - 1;
        dialog.querySelector('[data-viewer-count]').textContent = `${selected + 1} / ${photos.length}`;
        dialog.querySelector('[data-viewer-credit]').textContent = photo.credit;
        dialog.querySelector('[data-viewer-source]').href = photo.source_url;
        const license = dialog.querySelector('[data-viewer-license]');
        license.textContent = photo.license.label;
        license.href = photo.license.url;
        if (current !== photo) {
            current = photo;
            loadImage();
        }
    }

    function move(offset) {
        const { selected } = getSelection();
        select(selected + offset);
    }

    opener.addEventListener('click', () => {
        if (!getSelection().photos.length || dialog.open) return;
        current = null;
        dialog.showModal();
        main?.classList.add('fish-photo-viewer-open');
        update();
    });
    dialog.querySelector('[data-viewer-close]').addEventListener('click', () => dialog.close());
    dialog.addEventListener('close', () => {
        main?.classList.remove('fish-photo-viewer-open');
        opener.focus({ preventScroll: true });
    });
    let backdropPointer = false;
    dialog.addEventListener('pointerdown', (event) => { backdropPointer = event.target === dialog; });
    dialog.addEventListener('click', (event) => {
        if (backdropPointer && event.target === dialog) dialog.close();
        backdropPointer = false;
    });
    dialog.addEventListener('keydown', (event) => {
        if (!['ArrowLeft', 'ArrowRight'].includes(event.key)) return;
        event.preventDefault();
        event.stopPropagation();
        move(event.key === 'ArrowRight' ? 1 : -1);
    });
    previous.addEventListener('click', () => move(-1));
    next.addEventListener('click', () => move(1));
    dialog.querySelector('[data-viewer-retry]').addEventListener('click', loadImage);
    image.addEventListener('load', () => {
        loading = false;
        failed = false;
        renderImageState();
    });
    image.addEventListener('error', () => {
        if (!mediumFallback && current && image.getAttribute('src') !== current.url) {
            mediumFallback = true;
            image.src = current.url;
            return;
        }
        loading = false;
        failed = true;
        renderImageState();
    });
    return { update };
}
