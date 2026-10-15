@extends('layouts.authenticated')
@section('title', __('Beranda - Anufish'))

@section('page-content')
    <div class="home-content">
        <header>
            <h1 class="page-title">{{ __('Beranda') }}</h1>
            <p class="home-introduction">{{ __('Temukan ikan, kenali cirinya, dan pahami habitatnya.') }}</p>
        </header>

        <section class="home-hero" aria-labelledby="hero-title">
            <div class="home-hero-copy">
                <p class="home-eyebrow">{{ __('Eksplorasi bersama ANUFISH') }}</p>
                <h2 id="hero-title" class="hero-title">{{ __('Kenali ikan.') }}<br>{{ __('Pahami habitatnya.') }}</h2>
                <p class="home-hero-description">{{ __('Dari foto pertama hingga informasi spesies. Temukan nama ikan dan pelajari lebih jauh dengan bantuan AI serta data iNaturalist.') }}</p>
                <div class="home-scan-action">
                    <button type="button" disabled class="primary-action">{{ __('Mulai scan ikan') }}</button>
                    <span>{{ __('Segera') }}</span>
                </div>
                <a href="{{ route('fish.index') }}" class="home-text-link">{{ __('Jelajahi semua ikan') }} <span aria-hidden="true">→</span></a>
            </div>
            <figure class="home-hero-media">
                <img src="{{ asset('images/clownfish.jpg') }}" alt="{{ __('Ikan badut Amphiprion ocellaris di antara anemon') }}" width="416" height="264" fetchpriority="high">
                <figcaption>{{ __('Ikan badut') }} · <span class="italic">Amphiprion ocellaris</span>
                    <span class="home-photo-credit"><a href="https://commons.wikimedia.org/wiki/File:Amphiprion_ocellaris_(Clown_anemonefish)_Nemo.jpg">{{ __('Foto: Nick Hobgood') }}</a> · <a href="https://creativecommons.org/licenses/by-sa/3.0/">CC BY-SA 3.0</a></span>
                </figcaption>
            </figure>
        </section>

        <section class="home-shortcuts" aria-label="{{ __('Pintasan eksplorasi') }}">
            <x-home.shortcut icon="fish" :title="__('Cari informasi ikan')" :description="__('Nama, taksonomi, dan sumber spesies.')" :href="route('fish.index')" />
            <x-home.shortcut icon="history" :title="__('Lanjutkan pengamatan')" :description="__('Buka kembali hasil scan milikmu.')" />
            <x-home.shortcut icon="article" :title="__('Baca dan bagikan')" :description="__('Belajar dari tulisan komunitas.')" />
        </section>

        <section aria-labelledby="home-reading-title" class="home-reading">
            <header class="home-section-heading">
                <h2 id="home-reading-title">{{ __('Bacaan untukmu') }}</h2>
                <span class="home-coming-soon">{{ __('Segera') }}</span>
            </header>
            <div class="home-reading-empty">
                <span class="home-reading-icon" aria-hidden="true"><x-navigation.icon name="article" /></span>
                <div>
                    <h3>{{ __('Belum ada artikel untuk ditampilkan') }}</h3>
                    <p>{{ __('Artikel komunitas akan hadir di sini. Sementara itu, jelajahi informasi spesies dari iNaturalist.') }}</p>
                    <a href="{{ route('fish.index') }}" class="home-text-link">{{ __('Jelajahi Ikan') }} <span aria-hidden="true">→</span></a>
                </div>
            </div>
        </section>
    </div>
@endsection
