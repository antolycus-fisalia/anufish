@extends('layouts.authenticated')

@section('title', __('Artikel Saya - Anufish'))

@section('page-content')
    <div
        class="article-list-page"
        data-article-list
        data-endpoint="/artikel"
        data-error-load="{{ __('Daftar artikel belum dapat dimuat. Silakan coba lagi.') }}"
        data-error-delete="{{ __('Artikel belum dapat dihapus. Silakan coba lagi.') }}"
        data-status-waiting="{{ __('Menunggu') }}"
        data-status-approved="{{ __('Disetujui') }}"
        data-status-rejected="{{ __('Ditolak') }}"
        data-locale="{{ app()->getLocale() }}"
    >
        <header class="article-list-header">
            <div>
                <h1 class="page-title">
                    {{ __('Artikel Saya') }}
                </h1>

                <p>
                    {{ __('Kelola artikel yang kamu buat dan lihat status moderasinya.') }}
                </p>
            </div>

            <a
                href="/artikel-saya/buat"
                class="primary-action"
            >
                {{ __('Buat Artikel') }}
            </a>
        </header>

        <section
            class="notification-panel"
            data-notifications
            data-endpoint="{{ route('artikel-saya.notifikasi.index') }}"
            data-error-load="{{ __('article.notification.load_failed') }}"
            data-error-mark="{{ __('article.notification.mark_failed') }}"
            data-message-approved="{{ __('article.notification.message_approved') }}"
            data-message-rejected="{{ __('article.notification.message_rejected') }}"
            data-message-deleted="{{ __('article.notification.message_deleted') }}"
            data-label-approved="{{ __('article.notification.status_approved') }}"
            data-label-rejected="{{ __('article.notification.status_rejected') }}"
            data-label-deleted="{{ __('article.notification.status_deleted') }}"
            data-unread-count="{{ __('article.notification.unread_count') }}"
            data-locale="{{ app()->getLocale() }}"
        >
            <header class="notification-header">
                <div>
                    <h2 class="text-lg font-semibold text-anufish-navy" tabindex="-1" data-notification-heading>
                        {{ __('article.notification.title') }}
                    </h2>

                    <p class="mt-1 text-sm leading-[22px] text-anufish-muted">
                        {{ __('article.notification.description') }}
                    </p>
                </div>

                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <span
                        class="notification-unread"
                        data-notification-unread
                        role="status"
                        hidden
                    ></span>
                    <button type="button" class="notification-secondary-action" data-notification-reload disabled>
                        {{ __('article.notification.reload') }}
                    </button>
                </div>
            </header>

            <x-ui.state
                data-notification-loading
                state="loading"
                :title="__('Memuat notifikasi...')"
                :message="__('Mohon tunggu sementara notifikasi dimuat.')"
            />

            <x-ui.state
                data-notification-error
                state="failure"
                :title="__('article.notification.load_failed')"
                hidden
            >
                <x-slot:body>
                    <span data-notification-error-message></span>
                </x-slot:body>

                <button
                    type="button"
                    class="notification-secondary-action"
                    data-notification-retry
                >
                    {{ __('Coba lagi') }}
                </button>
            </x-ui.state>

            <x-ui.state
                data-notification-empty
                state="empty"
                :title="__('article.notification.empty_title')"
                :message="__('article.notification.empty_message')"
                hidden
            />

            <section
                class="notification-ready"
                data-notification-ready
                hidden
            >
                <div
                    class="notification-list"
                    data-notification-items
                ></div>

                <div class="mt-4 flex justify-end">
                    <button
                        type="button"
                        class="notification-secondary-action"
                        data-notification-mark-all
                    >
                        {{ __('article.notification.mark_all_read') }}
                    </button>
                </div>
            </section>

            <template data-notification-template>
                <article class="notification-item">
                    <div class="notification-item-main">
                        <div class="min-w-0">
                            <p
                                class="notification-message"
                                data-notification-message
                            ></p>

                            <div class="notification-meta">
                                <span
                                    class="notification-chip"
                                    data-notification-chip
                                ></span>

                                <time data-notification-time></time>
                            </div>

                            <p
                                class="notification-missing"
                                data-notification-missing
                                hidden
                            >
                                {{ __('article.notification.link_missing') }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <a
                                href="#"
                                class="notification-link"
                                data-notification-link
                            >
                                {{ __('article.notification.view_article') }}
                            </a>

                            <button
                                type="button"
                                class="notification-secondary-action"
                                data-notification-mark
                            >
                                {{ __('article.notification.mark_read') }}
                            </button>
                        </div>
                    </div>
                </article>
            </template>
        </section>

        <x-ui.state
            data-article-loading
            state="loading"
            :title="__('Memuat artikel...')"
            :message="__('Mohon tunggu sementara daftar artikel dimuat.')"
        />

        <x-ui.state
            data-article-error
            state="failure"
            :title="__('Daftar artikel belum dapat dimuat')"
            hidden
        >
            <x-slot:body>
                <span data-article-error-message></span>
            </x-slot:body>

            <button
                type="button"
                class="article-secondary-action"
                data-article-retry
            >
                {{ __('Coba lagi') }}
            </button>
        </x-ui.state>

        <x-ui.state
            data-article-empty
            state="empty"
            :title="__('Belum ada artikel')"
            :message="__('Artikel yang kamu buat akan muncul di sini beserta status moderasinya.')"
            hidden
        >
            <a
                href="/artikel-saya/buat"
                class="article-secondary-action"
            >
                {{ __('Buat Artikel Pertama') }}
            </a>
        </x-ui.state>

        <section
            class="article-list-panel"
            data-article-ready
            hidden
        >
            <div
                class="article-list"
                data-article-items
            ></div>

            <nav
                class="article-pagination"
                aria-label="{{ __('Navigasi artikel') }}"
                data-article-pagination
                hidden
            >
                <button
                    type="button"
                    class="article-secondary-action"
                    data-article-previous
                >
                    {{ __('Sebelumnya') }}
                </button>

                <p>
                    {{ __('Halaman') }}
                    <span data-article-current-page>1</span>
                    /
                    <span data-article-last-page>1</span>
                </p>

                <button
                    type="button"
                    class="article-secondary-action"
                    data-article-next
                >
                    {{ __('Selanjutnya') }}
                </button>
            </nav>
        </section>

        <template data-article-template>
            <article class="article-list-item">
                <div class="article-list-main">
                    <div>
                        <h2
                            class="article-list-title"
                            data-article-title
                        ></h2>

                        <div class="article-list-meta">
                            <span data-article-category></span>

                            <span aria-hidden="true">•</span>

                            <time data-article-date></time>
                        </div>
                    </div>

                    <span
                        class="article-status"
                        data-article-status
                    ></span>
                </div>

                <div class="article-list-actions">
                    <a
                        href="#"
                        class="article-secondary-action"
                        data-article-edit
                    >
                        {{ __('Edit') }}
                    </a>

                    <button
                        type="button"
                        class="article-danger-action"
                        data-article-delete
                    >
                        {{ __('Hapus') }}
                    </button>
                </div>
            </article>
        </template>
        <dialog data-article-delete-dialog aria-labelledby="article-delete-title"
            class="m-auto w-[calc(100%-2rem)] max-w-md rounded-2xl border border-anufish-border bg-white p-6 backdrop:bg-anufish-navy/40">
            <form method="dialog" class="space-y-4">
                <h2 id="article-delete-title" class="font-semibold text-anufish-navy">
                    {{ __('Hapus artikel ini? Tindakan ini tidak dapat dibatalkan.') }}
                </h2>
                <div class="flex flex-wrap justify-end gap-3">
                    <button value="cancel" autofocus class="secondary-action">{{ __('Batal') }}</button>
                    <button value="delete" class="primary-action">{{ __('Hapus') }}</button>
                </div>
            </form>
        </dialog>
    </div>
@endsection
