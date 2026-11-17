
@extends('layouts.authenticated')

@section('title', __('article.admin_moderation.detail_title') . ' - Anufish')

@section('page-content')
<div class="space-y-6">
    {{-- Header --}}
    <header>
        <a href="{{ $listUrl }}"
           class="inline-flex min-h-11 items-center text-sm font-semibold text-anufish-teal hover:underline focus-visible:outline-2 focus-visible:outline-anufish-teal">
            {{ __('article.admin_moderation.back_to_list') }}
        </a>

        <h1 class="mt-3 text-3xl font-bold text-anufish-navy">
            {{ __('article.admin_moderation.detail_title') }}
        </h1>
    </header>

    {{-- Detail Artikel --}}
    <article class="space-y-6 rounded-2xl border border-anufish-border bg-white p-4 sm:p-6">
        <div class="space-y-3">
            <span class="inline-block rounded-full bg-anufish-pale px-3 py-1 text-sm font-medium text-anufish-navy">
                {{ __('article.admin_moderation.statuses.' . $article->status) }}
            </span>

            <h2 class="break-words text-2xl font-bold text-anufish-navy">
                {{ $article->judul }}
            </h2>

            <dl class="grid gap-4 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-anufish-muted">
                        {{ __('article.admin_moderation.author') }}
                    </dt>
                    <dd class="mt-1 font-semibold text-anufish-navy">
                        {{ $article->user?->name ?? __('article.admin_moderation.unknown_author') }}
                    </dd>
                </div>

                <div>
                    <dt class="text-anufish-muted">
                        {{ __('article.admin_moderation.category') }}
                    </dt>
                    <dd class="mt-1 font-semibold text-anufish-navy">
                        {{ $article->kategori }}
                    </dd>
                </div>

                <div>
                    <dt class="text-anufish-muted">
                        {{ __('article.admin_moderation.created_at') }}
                    </dt>
                    <dd class="mt-1 text-anufish-navy">
                        {{ $article->created_at?->format('d M Y') }}
                    </dd>
                </div>
            </dl>
        </div>

        {{-- Konten Tersanitasi --}}
        <div class="border-t border-anufish-border pt-6">
            <h3 class="mb-4 text-lg font-semibold text-anufish-navy">
                {{ __('article.admin_moderation.content') }}
            </h3>

            <x-article.content :html="$article->isi" />
        </div>
    </article>

    {{-- Aksi Moderasi --}}
    <section
        data-moderation-panel
        data-error="{{ __('article.admin_moderation.request_failed') }}"
        data-missing="{{ __('article.admin_moderation.article_unavailable') }}"
        class="rounded-2xl border border-anufish-border bg-white p-4 sm:p-6"
    >
        <h2 class="text-lg font-semibold text-anufish-navy">
            {{ __('article.admin_moderation.actions_title') }}
        </h2>

        {{-- Feedback sukses / gagal --}}
        <p
            data-moderation-feedback
            aria-live="polite"
            class="mt-3 hidden rounded-lg bg-anufish-pale p-3 text-sm text-anufish-navy"
        ></p>

        {{-- Refresh ketika revision conflict --}}
        <a
            data-moderation-refresh
            href="{{ request()->fullUrl() }}"
            class="mt-3 hidden text-sm font-semibold text-anufish-teal underline"
        >
            {{ __('article.admin_moderation.refresh_detail') }}
        </a>

        <a
            data-moderation-list
            href="{{ $listUrl }}"
            class="mt-3 hidden text-sm font-semibold text-anufish-teal underline"
        >
            {{ __('article.admin_moderation.back_to_list') }}
        </a>

        <div class="mt-4 flex flex-wrap gap-3">

            {{-- Hanya artikel menunggu yang dapat disetujui atau ditolak --}}
            @if ($article->status === 'menunggu')

                {{-- Setujui Artikel --}}
                <form
                    method="POST"
                    action="{{ route('admin.artikel.approve', $article->id) }}"
                    data-moderation-form
                    data-operation="approve"
                >
                    @csrf

                    <input
                        type="hidden"
                        name="moderation_revision"
                        value="{{ $moderationRevision }}"
                    >

                    <button
                        type="submit"
                        class="min-h-11 rounded-lg bg-anufish-teal px-5 font-semibold text-white hover:bg-anufish-navy disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {{ __('article.admin_moderation.approve') }}
                    </button>
                </form>

                {{-- Tolak Artikel --}}
                <form
                    method="POST"
                    action="{{ route('admin.artikel.reject', $article->id) }}"
                    data-moderation-form
                    data-operation="reject"
                >
                    @csrf

                    <input
                        type="hidden"
                        name="moderation_revision"
                        value="{{ $moderationRevision }}"
                    >

                    <button
                        type="submit"
                        class="min-h-11 rounded-lg border border-anufish-border px-5 font-semibold text-anufish-navy hover:bg-anufish-pale disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {{ __('article.admin_moderation.reject') }}
                    </button>
                </form>

            @endif

            {{-- Hapus Artikel --}}
            <form
                method="POST"
                action="{{ route('admin.artikel.delete', $article->id) }}"
                data-moderation-form
                data-operation="delete"
                data-redirect="{{ $listUrl }}"
            >
                @csrf

                <input
                    type="hidden"
                    name="moderation_revision"
                    value="{{ $moderationRevision }}"
                >

                <button
                    type="button"
                    class="min-h-11 rounded-lg bg-red-700 px-5 font-semibold text-white hover:bg-red-800 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {{ __('article.admin_moderation.delete') }}
                </button>
            </form>

        </div>

        <dialog data-moderation-delete-dialog aria-labelledby="moderation-delete-title"
            class="m-auto w-[calc(100%-2rem)] max-w-md rounded-2xl border border-anufish-border bg-white p-6 backdrop:bg-anufish-navy/40">
            <form method="dialog" class="space-y-4">
                <h2 id="moderation-delete-title" class="font-semibold text-anufish-navy">
                    {{ __('article.admin_moderation.confirm_delete') }}
                </h2>
                <div class="flex flex-wrap justify-end gap-3">
                    <button value="cancel" autofocus class="secondary-action">{{ __('Batal') }}</button>
                    <button value="delete" class="min-h-11 rounded-lg bg-red-700 px-5 font-semibold text-white hover:bg-red-800">
                        {{ __('article.admin_moderation.delete') }}
                    </button>
                </div>
            </form>
        </dialog>
    </section>
</div>
@endsection
