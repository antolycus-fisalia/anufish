export function initModerationNotifications() {
    const root = document.querySelector('[data-notifications]');

    if (!root) {
        return;
    }

    const loading = root.querySelector('[data-notification-loading]');
    const errorState = root.querySelector('[data-notification-error]');
    const errorMessage = root.querySelector('[data-notification-error-message]');
    const emptyState = root.querySelector('[data-notification-empty]');
    const ready = root.querySelector('[data-notification-ready]');
    const items = root.querySelector('[data-notification-items]');
    const template = root.querySelector('[data-notification-template]');
    const unreadBadge = root.querySelector('[data-notification-unread]');
    const retryButton = root.querySelector('[data-notification-retry]');
    const markAllButton = root.querySelector('[data-notification-mark-all]');
    const reloadButton = root.querySelector('[data-notification-reload]');
    const heading = root.querySelector('[data-notification-heading]');

    let busy = false;
    let notifications = [];
    let unreadCount = 0;

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    function hideStates() {
        loading.hidden = true;
        errorState.hidden = true;
        emptyState.hidden = true;
        ready.hidden = true;
    }

    function showError(message) {
        hideStates();

        errorMessage.textContent = message;
        errorState.hidden = false;
    }

    function actionLabel(action) {
        switch (action) {
            case 'approve':
                return root.dataset.labelApproved;
            case 'reject':
                return root.dataset.labelRejected;
            case 'delete':
                return root.dataset.labelDeleted;
            default:
                return '-';
        }
    }

    function messagePattern(action) {
        switch (action) {
            case 'approve':
                return root.dataset.messageApproved;
            case 'reject':
                return root.dataset.messageRejected;
            case 'delete':
                return root.dataset.messageDeleted;
            default:
                return '';
        }
    }

    function formatTime(value) {
        if (!value) {
            return '-';
        }

        const date = new Date(value);

        if (Number.isNaN(date.getTime())) {
            return '-';
        }

        const locale = root.dataset.locale === 'en' ? 'en-US' : 'id-ID';

        return new Intl.DateTimeFormat(locale, {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        }).format(date);
    }

    function updateBadge() {
        if (!unreadBadge) {
            return;
        }

        unreadBadge.textContent = root.dataset.unreadCount.replace(':count', unreadCount);
        unreadBadge.hidden = unreadCount === 0;
    }

    function updateControls() {
        reloadButton.disabled = busy;
        retryButton.disabled = busy;
        markAllButton.disabled = busy || unreadCount === 0;
        items.querySelectorAll('[data-notification-mark]').forEach((button) => {
            button.disabled = busy;
        });
    }

    function renderNotification(notification) {
        const fragment = template.content.cloneNode(true);
        const item = fragment.querySelector('.notification-item');
        const message = fragment.querySelector('[data-notification-message]');
        const time = fragment.querySelector('[data-notification-time]');
        const chip = fragment.querySelector('[data-notification-chip]');
        const link = fragment.querySelector('[data-notification-link]');
        const missing = fragment.querySelector('[data-notification-missing]');
        const markButton = fragment.querySelector('[data-notification-mark]');

        message.textContent = messagePattern(notification.action).replace(
            ':title',
            () => notification.judul ?? '-',
        );

        time.textContent = formatTime(notification.created_at);
        time.dateTime = notification.created_at ?? '';

        chip.textContent = actionLabel(notification.action);
        chip.dataset.action = notification.action ?? '';

        item.classList.toggle('notification-item-unread', !notification.read);
        item.dataset.notificationId = notification.id;

        if (notification.link) {
            link.href = notification.link;
        } else {
            link.hidden = true;
            missing.hidden = false;
        }

        if (notification.read) {
            markButton.hidden = true;
        } else {
            markButton.addEventListener('click', () => {
                markRead(notification.id);
            });
        }

        return fragment;
    }

    function renderNotifications() {
        items.replaceChildren();

        notifications.forEach((notification) => {
            items.appendChild(renderNotification(notification));
        });

        updateBadge();
    }

    async function runRequest(operation, errorLabel) {
        if (busy) {
            return;
        }

        busy = true;
        updateControls();

        try {
            await operation();
        } catch {
            unreadBadge.hidden = true;
            showError(errorLabel);
        } finally {
            busy = false;
            updateControls();
        }
    }

    async function request(path = '', method = 'GET') {
        const response = await fetch(`${root.dataset.endpoint}${path}`, {
            method,
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                ...(method === 'POST' && csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
            },
        });

        if (!response.ok) {
            throw new Error(`Notification request failed (${response.status})`);
        }

        return response.json();
    }

    async function fetchNotifications() {
        hideStates();
        loading.hidden = false;

        const payload = await request();
        notifications = payload.data;
        unreadCount = payload.unread_count;

        hideStates();
        renderNotifications();
        emptyState.hidden = notifications.length !== 0;
        ready.hidden = notifications.length === 0;
    }

    function load() {
        return runRequest(fetchNotifications, root.dataset.errorLoad);
    }

    function markRead(notificationId) {
        return runRequest(async () => {
            const payload = await request(`/${notificationId}/baca`, 'POST');
            notifications = notifications.map((notification) =>
                notification.id === notificationId ? payload.data : notification,
            );
            unreadCount = payload.unread_count;
            renderNotifications();

            const item = items.querySelector(`[data-notification-id="${notificationId}"]`);
            const link = item?.querySelector('[data-notification-link]:not([hidden])');
            (link ?? heading).focus();
        }, root.dataset.errorMark);
    }

    retryButton.addEventListener('click', load);
    reloadButton.addEventListener('click', load);
    markAllButton.addEventListener('click', () => {
        runRequest(async () => {
            await request('/baca-semua', 'POST');
            await fetchNotifications();
            heading.focus();
        }, root.dataset.errorMark);
    });

    load();
}
