@extends('layouts.authenticated')

@section('title', __('Artikel Saya - Anufish'))

@section('page-content')
    <div
        class="article-list-page"
        data-article-list
        data-endpoint="/artikel"
        data-error-load="{{ __('Daftar artikel belum dapat dimuat. Silakan coba lagi.') }}"
        data-error-delete="{{ __('Artikel belum dapat dihapus. Silakan coba lagi.') }}"
        data-confirm-delete="{{ __('Hapus artikel ini? Tindakan ini tidak dapat dibatalkan.') }}"
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
    </div>
@endsection
