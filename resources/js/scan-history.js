export function initHistoryImages() {
    document.querySelectorAll('[data-history-image]').forEach((image) => {
        const failed = () => {
            image.hidden = true;
            const message = image.closest('[data-history-photo]')?.querySelector('[data-history-image-error]');
            if (message) message.hidden = false;
        };
        image.addEventListener('error', failed, {once: true});
        if (image.complete && image.naturalWidth === 0) failed();
    });
}
