
@extends('layouts.authenticated')

@section('title', __('article.admin_moderation.list_title') . ' - Anufish')

@section('page-content')
<div class="space-y-6">
    <header>
        <h1 class="text-3xl font-bold text-anufish-navy">
            {{ __('article.admin_moderation.list_title') }}
        </h1>

        <p class="mt-2 text-anufish-muted">
            {{ __('article.admin_moderation.list_description') }}
        </p>
    </header>

    <section class="rounded-2xl border border-anufish-border bg-white p-4 sm:p-6">
        <form method="GET"
              action="{{ route('admin.artikel.index') }}"
              class="flex flex-col gap-3 sm:flex-row sm:items-end">

            <div class="w-full sm:max-w-xs">
                <label for="status"
                       class="mb-2 block text-sm font-semibold text-anufish-navy">
                    {{ __('article.admin_moderation.filter_status') }}
                </label>

                <select id="status"
                        name="status"
                        class="min-h-11 w-full rounded-lg border border-anufish-border bg-white px-4 focus:border-anufish-teal focus:outline-none focus:ring-2 focus:ring-anufish-teal/20">

                    <option value="" @selected($status === '')>
                        {{ __('article.admin_moderation.all_statuses') }}
                    </option>

                    @foreach (['menunggu', 'disetujui', 'ditolak'] as $option)
                        <option value="{{ $option }}" @selected($status === $option)>
                            {{ __('article.admin_moderation.statuses.' . $option) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit"
                    class="min-h-11 rounded-lg bg-anufish-teal px-5 font-semibold text-white hover:bg-anufish-navy">
                {{ __('article.admin_moderation.apply_filter') }}
            </button>
        </form>
    </section>

    <section class="overflow-hidden rounded-2xl border border-anufish-border bg-white">
        @forelse ($articles as $article)
            <article class="flex flex-col gap-4 border-b border-anufish-border/50 p-4 last:border-b-0 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                <div class="min-w-0 space-y-2">
                    <h2 class="break-words font-semibold text-anufish-navy">
                        {{ $article->judul }}
                    </h2>

                    <p class="text-sm text-anufish-muted">
                        {{ __('article.admin_moderation.author') }}:
                        {{ $article->user?->name ?? __('article.admin_moderation.unknown_author') }}
                    </p>

                    <div class="flex flex-wrap items-center gap-3 text-sm">
                        <span class="rounded-full bg-anufish-pale px-3 py-1 font-medium text-anufish-navy">
                            {{ __('article.admin_moderation.statuses.' . $article->status) }}
                        </span>

                        <span class="text-anufish-muted">
                            {{ $article->created_at?->format('d M Y') }}
                        </span>
                    </div>
                </div>

                <a href="{{ route('admin.artikel.show', $article->id) }}"
                   class="inline-flex min-h-11 shrink-0 items-center justify-center rounded-lg border border-anufish-border px-4 font-semibold text-anufish-teal hover:bg-anufish-pale focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-anufish-teal">
                    {{ __('article.admin_moderation.view_detail') }}
                </a>
            </article>
        @empty
            <div class="p-8 text-center">
                <p class="font-semibold text-anufish-navy">
                    {{ __('article.admin_moderation.empty') }}
                </p>
            </div>
        @endforelse
    </section>

    @if ($articles->hasPages())
        <nav aria-label="{{ __('article.admin_moderation.pagination') }}">
            {{ $articles->links() }}
        </nav>
    @endif
</div>
@endsection
