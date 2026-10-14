@extends('layouts.app')

@section('document-class', 'guest-page')

@section('content')
    <main class="guest-shell">
        <aside class="guest-editorial" aria-label="{{ __('Tentang Anufish') }}">
            <div class="w-fit rounded-lg bg-white p-4">
                <div class="brand-logo brand-logo-editorial"><img src="{{ asset('images/anufish-logo.png') }}" alt="Anufish"
                        width="475" height="488"></div>
            </div>
            <h2 class="hero-title text-white">{{ __('Dunia ikan,') }}<br>{{ __('lebih dekat.') }}</h2>
            <p class="text-base leading-6 text-white">
                {{ __('Jelajahi informasi spesies dan kenali dunia ikan melalui sumber pengetahuan yang tepercaya.') }}</p>
            <figure class="space-y-4">
                <img src="{{ asset('images/clownfish.jpg') }}" alt="{{ __('Ikan badut di antara anemon') }}" width="440"
                    height="320" class="aspect-[11/8] w-full rounded-lg object-cover">
                <figcaption class="text-xs leading-[18px] text-white">Amphiprion ocellaris<br>{{ __('Foto:') }} <a
                        href="https://commons.wikimedia.org/wiki/File:Amphiprion_ocellaris_(Clown_anemonefish)_Nemo.jpg"
                        class="underline underline-offset-2">Nick Hobgood</a> · <a
                        href="https://creativecommons.org/licenses/by-sa/3.0/" class="underline underline-offset-2">CC BY-SA
                        3.0</a></figcaption>
            </figure>
        </aside>
        <div class="guest-form-area">
            <div class="guest-form-fit">
                @yield('page-content')
            </div>
        </div>
    </main>
@endsection
