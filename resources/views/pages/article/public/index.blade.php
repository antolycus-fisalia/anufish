
@extends('layouts.authenticated')

@section('page-content')
<div class="space-y-6">

    <header class="space-y-2">
        <h1 class="text-3xl font-bold text-anufish-navy">
            {{ __('article.public.title') }}
        </h1>

        <p class="text-sm text-anufish-muted">
            {{ __('article.public.description') }}
        </p>
    </header>

    @if ($artikel->isEmpty())
        <section class="rounded-2xl border border-anufish-border bg-white px-6 py-12 text-center">
            <h2 class="text-lg font-semibold text-anufish-navy">
                {{ __('article.public.empty_title') }}
            </h2>

            <p class="mt-2 text-sm text-anufish-muted">
                {{ __('article.public.empty_description') }}
            </p>
        </section>
    @else
        <section
            aria-label="{{ __('article.public.title') }}"
            class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3"
        >
            @foreach ($artikel as $item)
                <article class="min-w-0 overflow-hidden rounded-2xl border border-anufish-border bg-white">

                    <div class="flex aspect-[16/10] items-center justify-center bg-anufish-pale">
                        @if ($item->gambar && str_starts_with($item->gambar, 'artikel-images/'))
                            <img
                                src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->gambar) }}"
                                alt="{{ $item->judul }}"
                                loading="lazy"
                                class="h-full w-full object-cover"
                            >
                        @else
                            <span class="px-4 text-center text-sm text-anufish-muted">
                                {{ __('article.public.no_image') }}
                            </span>
                        @endif
                    </div>

                    <div class="space-y-3 p-5">
                        <p class="text-xs font-semibold text-anufish-teal">
                            {{ $item->kategori }}
                        </p>

                        <h2 class="break-words text-lg font-semibold text-anufish-navy">
                            {{ $item->judul }}
                        </h2>

                        <p class="break-words text-sm leading-6 text-anufish-muted">
                            {{ \Illuminate\Support\Str::limit(
                                trim(preg_replace('/\s+/u', ' ', strip_tags($item->isi))),
                                150
                            ) }}
                        </p>

                        <div class="border-t border-anufish-border/50 pt-3">
                            <p class="text-xs text-anufish-muted">
                                {{ __('article.public.author') }}:
                                {{ $item->user?->name ?? __('article.public.unknown_author') }}
                            </p>

                            <p class="mt-1 text-xs text-anufish-muted">
                                {{ $item->created_at?->format('d M Y') }}
                            </p>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>

        @if ($artikel->hasPages())
            <nav
                aria-label="{{ __('article.public.pagination') }}"
                class="pt-2"
            >
                {{ $artikel->links() }}
            </nav>
        @endif
    @endif
</div>
@endsection
