export function initArticleList() {
    const root = document.querySelector('[data-article-list]');

    if (!root) {
        return;
    }

    const loading = root.querySelector('[data-article-loading]');
    const errorState = root.querySelector('[data-article-error]');
    const errorMessage = root.querySelector('[data-article-error-message]');
    const emptyState = root.querySelector('[data-article-empty]');
    const ready = root.querySelector('[data-article-ready]');
    const items = root.querySelector('[data-article-items]');
    const template = root.querySelector('[data-article-template]');

    const pagination = root.querySelector('[data-article-pagination]');
    const previousButton = root.querySelector('[data-article-previous]');
    const nextButton = root.querySelector('[data-article-next]');
    const currentPageElement = root.querySelector('[data-article-current-page]');
    const lastPageElement = root.querySelector('[data-article-last-page]');
    const retryButton = root.querySelector('[data-article-retry]');

    let currentPage = 1;
    let lastPage = 1;
    let requestedPage = 1;
    let loadingRequest = false;
    let deletingId = null;
    const deleteDialog = root.querySelector('[data-article-delete-dialog]');

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    function hideStates() {
        loading.hidden = true;
        errorState.hidden = true;
        emptyState.hidden = true;
        ready.hidden = true;
    }

    function showLoading() {
        hideStates();
        loading.hidden = false;
    }

    function showError(message) {
        hideStates();

        errorMessage.textContent = message;
        errorState.hidden = false;
    }

    function showEmpty() {
        hideStates();
        emptyState.hidden = false;
    }

    function statusLabel(status) {
        switch (status) {
            case 'menunggu':
                return root.dataset.statusWaiting;

            case 'disetujui':
                return root.dataset.statusApproved;

            case 'ditolak':
                return root.dataset.statusRejected;

            default:
                return status ?? '-';
        }
    }

    function formatDate(value) {
        if (!value) {
            return '-';
        }

        const date = new Date(value);

        if (Number.isNaN(date.getTime())) {
            return '-';
        }

        const locale = root.dataset.locale === 'en'
            ? 'en-US'
            : 'id-ID';

        return new Intl.DateTimeFormat(locale, {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
        }).format(date);
    }

    function updatePagination() {
        currentPageElement.textContent = currentPage;
        lastPageElement.textContent = lastPage;

        previousButton.disabled = currentPage <= 1;
        nextButton.disabled = currentPage >= lastPage;

        pagination.hidden = lastPage <= 1;
    }

    function renderArticle(article) {
        const fragment = template.content.cloneNode(true);

        const card = fragment.querySelector('.article-list-item');
        const title = fragment.querySelector('[data-article-title]');
        const category = fragment.querySelector('[data-article-category]');
        const date = fragment.querySelector('[data-article-date]');
        const status = fragment.querySelector('[data-article-status]');
        const edit = fragment.querySelector('[data-article-edit]');
        const deleteButton = fragment.querySelector('[data-article-delete]');

        title.textContent = article.judul ?? '-';
        category.textContent = article.kategori ?? '-';

        const dateValue = article.updated_at ?? article.created_at;

        date.textContent = formatDate(dateValue);

        if (dateValue) {
            date.dateTime = dateValue;
        }

        status.textContent = statusLabel(article.status);
        status.dataset.status = article.status ?? '';

        edit.href = `/artikel-saya/${article.id}/edit`;

        deleteButton.addEventListener('click', () => {
            deleteArticle(article.id, deleteButton);
        });

        card.dataset.articleId = article.id;

        return fragment;
    }

    function renderArticles(articles) {
        items.replaceChildren();

        articles.forEach((article) => {
            items.appendChild(renderArticle(article));
        });
    }

    async function loadArticles(page = 1) {
        if (loadingRequest || page < 1) {
            return;
        }

        loadingRequest = true;
        requestedPage = page;
        showLoading();

        try {
            const response = await fetch(
                `${root.dataset.endpoint}?page=${page}`,
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

            if (!Array.isArray(payload.data)) {
                throw new Error(root.dataset.errorLoad);
            }

            currentPage = Number(payload.current_page) || 1;
            lastPage = Number(payload.last_page) || 1;

            if (payload.data.length === 0) {
                if (currentPage > 1) {
                    loadingRequest = false;
                    await loadArticles(Math.min(currentPage - 1, lastPage));

                    return;
                }

                showEmpty();

                return;
            }

            renderArticles(payload.data);
            updatePagination();

            hideStates();
            ready.hidden = false;
        } catch (error) {
            showError(
                error instanceof Error
                    ? error.message
                    : root.dataset.errorLoad,
            );
        } finally {
            loadingRequest = false;
        }
    }

    async function deleteArticle(articleId, button) {
        if (deletingId !== null || deleteDialog.open) {
            return;
        }

        deleteDialog.returnValue = '';
        deleteDialog.showModal();
        const confirmed = await new Promise((resolve) => {
            deleteDialog.addEventListener('close', () => {
                resolve(deleteDialog.returnValue === 'delete');
            }, { once: true });
        });

        if (!confirmed) {
            return;
        }

        deletingId = articleId;
        button.disabled = true;

        try {
            const response = await fetch(`/artikel/${articleId}`, {
                method: 'DELETE',
                headers: {
                    Accept: 'application/json',
                    ...(csrfToken
                        ? { 'X-CSRF-TOKEN': csrfToken }
                        : {}),
                },
                credentials: 'same-origin',
            });

            const payload = await response.json();

            if (!response.ok) {
                throw new Error(
                    payload.message || root.dataset.errorDelete,
                );
            }

            await loadArticles(currentPage);
        } catch (error) {
            showError(
                error instanceof Error
                    ? error.message
                    : root.dataset.errorDelete,
            );
        } finally {
            deletingId = null;
            button.disabled = false;
        }
    }

    previousButton.addEventListener('click', () => {
        loadArticles(currentPage - 1);
    });

    nextButton.addEventListener('click', () => {
        loadArticles(currentPage + 1);
    });

    retryButton.addEventListener('click', () => {
        loadArticles(requestedPage);
    });

    loadArticles();
}
