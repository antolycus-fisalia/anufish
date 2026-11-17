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

    let requestGeneration = 0;
    let markingId = null;
    let markingAll = false;
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

        unreadBadge.textContent = unreadCount;
        unreadBadge.hidden = unreadCount === 0;
    }

    function updateMarkAllButton() {
        if (!markAllButton) {
            return;
        }

        markAllButton.disabled =
            unreadCount === 0 ||
            markingAll ||
            notifications.length === 0;
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
            notification.judul ?? '-',
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
                markRead(notification.id, markButton);
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
        updateMarkAllButton();
    }

    async function load() {
        const generation = ++requestGeneration;
        hideStates();
        loading.hidden = false;

        try {
            const response = await fetch(
                root.dataset.endpoint,
                {
                    headers: {
                        Accept: 'application/json',
                    },
                    credentials: 'same-origin',
                },
            );

            const payload = await response.json();

            if (!response.ok) {
                throw new Error(
                    payload.message || root.dataset.errorLoad,
                );
            }

            if (generation !== requestGeneration) {
                return;
            }

            notifications = Array.isArray(payload.data) ? payload.data : [];
            unreadCount = Number(payload.unread_count) || 0;

            hideStates();

            if (notifications.length === 0) {
                emptyState.hidden = false;

                return;
            }

            renderNotifications();
            ready.hidden = false;
        } catch (error) {
            if (generation !== requestGeneration) {
                return;
            }

            showError(
                error instanceof Error
                    ? error.message
                    : root.dataset.errorLoad,
            );
        }
    }

    async function markRead(notificationId, button) {
        if (markingId !== null || markingAll) {
            return;
        }

        markingId = notificationId;
        button.disabled = true;

        try {
            const response = await fetch(
                `${root.dataset.endpoint}/${notificationId}/baca`,
                {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        ...(csrfToken
                            ? { 'X-CSRF-TOKEN': csrfToken }
                            : {}),
                    },
                    credentials: 'same-origin',
                },
            );

            const payload = await response.json();

            if (!response.ok) {
                throw new Error(
                    payload.message || root.dataset.errorMark,
                );
            }

            notifications = notifications.map((notification) =>
                notification.id === notificationId
                    ? payload.data
                    : notification,
            );

            unreadCount = Math.max(0, unreadCount - 1);

            renderNotifications();
        } catch (error) {
            showError(
                error instanceof Error
                    ? error.message
                    : root.dataset.errorMark,
            );
        } finally {
            markingId = null;
        }
    }

    async function markAllRead() {
        if (markingAll || unreadCount === 0) {
            return;
        }

        markingAll = true;
        updateMarkAllButton();

        try {
            const response = await fetch(
                `${root.dataset.endpoint}/baca-semua`,
                {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        ...(csrfToken
                            ? { 'X-CSRF-TOKEN': csrfToken }
                            : {}),
                    },
                    credentials: 'same-origin',
                },
            );

            const payload = await response.json();

            if (!response.ok) {
                throw new Error(
                    payload.message || root.dataset.errorMark,
                );
            }

            await load();
        } catch (error) {
            showError(
                error instanceof Error
                    ? error.message
                    : root.dataset.errorMark,
            );
        } finally {
            markingAll = false;
            updateMarkAllButton();
        }
    }

    retryButton?.addEventListener('click', () => {
        load();
    });

    reloadButton?.addEventListener('click', () => {
        load();
    });

    markAllButton?.addEventListener('click', () => {
        markAllRead();
    });

    load();
}
