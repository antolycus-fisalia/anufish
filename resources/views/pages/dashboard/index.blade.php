@extends('layouts.authenticated')

@section('title', request()->filled('q') ? __('Jelajahi Ikan - Anufish') : __('Beranda - Anufish'))

@section('page-content')
    @if (request()->filled('q'))
        <a href="{{ route('homepage') }}"
            class="mb-4 inline-flex min-h-11 items-center gap-2 rounded-lg text-sm font-semibold text-anufish-teal hover:text-anufish-navy focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-anufish-teal">
            <span aria-hidden="true">←</span>
            {{ __('Kembali ke Beranda') }}
        </a>
    @endif
    <header class="mb-6">
        <h1 class="page-title">{{ request()->filled('q') ? __('Jelajahi Ikan') : __('Beranda') }}</h1>
        <p class="mt-2 text-sm leading-[22px] text-anufish-muted">
            {{ __('Temukan ikan, kenali cirinya, dan pahami habitatnya.') }}</p>
    </header>

    @unless (request()->filled('q'))
        <section aria-labelledby="hero-title"
            class="mb-6 grid gap-8 rounded-2xl border border-anufish-border bg-anufish-pale p-6 xl:grid-cols-[minmax(0,1fr)_416px] xl:p-8">
            <div class="min-w-0 space-y-4">
                <p class="text-xs font-medium leading-[18px] text-anufish-teal">{{ __('Eksplorasi bersama ANUFISH') }}</p>
                <h2 id="hero-title" class="hero-title text-anufish-navy">
                    {{ __('Kenali ikan.') }}<br>{{ __('Pahami habitatnya.') }}</h2>
                <p class="max-w-xl text-base leading-6 text-anufish-muted">
                    {{ __('Temukan nama ikan, jelajahi taksonomi spesies, dan pelajari lebih jauh melalui data iNaturalist.') }}
                </p>
                <a href="#fish-search" class="primary-action w-full sm:w-55">{{ __('Jelajahi ikan') }}</a>
            </div>
            <figure class="min-w-0 space-y-2">
                <img src="{{ asset('images/clownfish.jpg') }}"
                    alt="{{ __('Ikan badut Amphiprion ocellaris di antara anemon') }}" width="416" height="264"
                    class="h-48 w-full rounded-lg object-cover sm:h-66">
                <figcaption class="text-xs leading-[18px] text-anufish-navy">{{ __('Ikan badut') }} · <span
                        class="italic">Amphiprion ocellaris</span><br><a
                        href="https://commons.wikimedia.org/wiki/File:Amphiprion_ocellaris_(Clown_anemonefish)_Nemo.jpg"
                        class="text-anufish-muted underline underline-offset-2">{{ __('Foto: Nick Hobgood') }}</a> · <a
                        href="https://creativecommons.org/licenses/by-sa/3.0/"
                        class="text-anufish-muted underline underline-offset-2">CC BY-SA 3.0</a></figcaption>
            </figure>
        </section>
    @endunless

    <section id="fish-search" aria-label="{{ __('Pencarian ikan') }}" class="scroll-mt-6">
        @unless (request()->filled('q'))
            <h2 class="mb-4 text-2xl font-semibold leading-8 text-anufish-navy">{{ __('Jelajahi Ikan') }}</h2>
        @endunless
        @include('pages.dashboard.partials.fish-search')
    </section>
@endsection
