export function initAuthLayout() {
    const area = document.querySelector('.guest-form-area');
    if (!area) return;
    const frame = area.querySelector('.guest-form-fit');
    const card = frame.querySelector('article');
    const shell = document.querySelector('.guest-shell');
    function fitAuthForm() {
        const styles = getComputedStyle(area);
        const availableHeight = area.clientHeight - parseFloat(styles.paddingTop) - parseFloat(styles.paddingBottom);
        const scale = Math.min(1, frame.clientWidth / card.offsetWidth, Math.max(0, availableHeight) / card.offsetHeight);
        frame.style.setProperty('--auth-scale', scale);
        frame.style.height = `${card.offsetHeight * scale}px`;
    }
    function syncViewport() {
        shell.style.height = `${window.visualViewport?.height ?? window.innerHeight}px`;
        fitAuthForm();
    }
    const observer = new ResizeObserver(fitAuthForm);
    observer.observe(area);
    observer.observe(card);
    window.addEventListener('resize', syncViewport);
    window.visualViewport?.addEventListener('resize', syncViewport);
    document.fonts.ready.then(fitAuthForm);
    syncViewport();
}
