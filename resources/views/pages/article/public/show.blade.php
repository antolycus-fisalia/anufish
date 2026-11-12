
@extends('layouts.authenticated')

@section('page-content')
<div class="mx-auto max-w-4xl space-y-6">

    <header class="space-y-4">
        <a
            href="{{ url('/artikel-publik') }}"
            class="inline-flex min-h-11 items-center text-sm font-semibold text-anufish-teal hover:underline focus-visible:outline-2 focus-visible:outline-anufish-teal"
        >
            {{ __('article.public.back_to_list') }}
        </a>

        <div class="space-y-3">
            <p class="text-sm font-semibold text-anufish-teal">
                {{ $article->kategori }}
            </p>

            <h1 class="break-words text-3xl font-bold text-anufish-navy sm:text-4xl">
                {{ $article->judul }}
            </h1>

            <div class="flex flex-wrap gap-x-4 gap-y-2 text-sm text-anufish-muted">
                <span>
                    {{ __('article.public.author') }}:
                    {{ $article->user?->name ?? __('article.public.unknown_author') }}
                </span>

                <time datetime="{{ $article->created_at?->toDateString() }}">
                    {{ $article->created_at?->format('d M Y') }}
                </time>
            </div>
        </div>
    </header>

    @if (
        $article->gambar &&
        str_starts_with($article->gambar, 'artikel-images/') &&
        ! str_contains($article->gambar, '..')
    )
        <figure class="overflow-hidden rounded-2xl border border-anufish-border bg-white">
            <img
                src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($article->gambar) }}"
                alt="{{ $article->judul }}"
                class="max-h-[480px] w-full object-contain"
            >
        </figure>
    @endif

    <article class="min-w-0 rounded-2xl border border-anufish-border bg-white p-4 sm:p-6">
        <div class="article-content break-words text-anufish-navy">
            <x-article.content :html="$article->isi" />
        </div>
    </article>
</div>
@endsection
