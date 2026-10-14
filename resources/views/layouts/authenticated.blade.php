@extends('layouts.app')
@section('document-class', 'h-dvh overflow-hidden')
@section('body-class', 'h-dvh min-h-0 overflow-hidden')
@section('content')
    <div data-navigation-shell
        class="flex h-dvh overflow-hidden bg-gray-50">
        <a href="#main-content"
            class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:p-3">{{ __('Lewati ke konten') }}</a>
        <aside class="hidden h-full w-64 shrink-0 border-r border-anufish-border/50 lg:block"><x-navigation.sidebar />
        </aside>
        <dialog data-navigation-dialog aria-label="{{ __('Menu navigasi') }}"

            class="m-0 h-dvh max-h-dvh w-72 max-w-full border-0 p-0 backdrop:bg-anufish-navy/40">
            <x-navigation.sidebar><button type="button" data-navigation-close
                    aria-label="{{ __('Tutup navigasi') }}"
                    class="flex size-11 items-center justify-center rounded-lg text-anufish-navy hover:bg-anufish-pale focus-visible:outline-2 focus-visible:outline-anufish-teal"><x-navigation.icon
                        name="close" /></button></x-navigation.sidebar>
        </dialog>
        <div class="flex min-h-0 min-w-0 flex-1 flex-col">
            <header
                class="flex h-16 shrink-0 items-center gap-3 border-b border-anufish-border/50 bg-white px-4 sm:px-6 lg:px-8">
                <button data-navigation-open type="button"
                    aria-label="{{ __('Buka navigasi') }}" aria-haspopup="dialog"
                    class="flex size-11 items-center justify-center rounded-lg text-anufish-navy hover:bg-white focus-visible:outline-2 focus-visible:outline-anufish-teal lg:hidden"><x-navigation.icon
                        name="menu" /></button>
                <p class="min-w-0 flex-1 truncate text-sm text-anufish-muted"><span
                        class="hidden sm:inline">{{ __('Ruang eksplorasi / ') }}</span>{{ request()->routeIs('fish.show') ? __('Detail Ikan') : (request()->routeIs('profile.*') ? __('Profil Saya') : (request()->filled('q') ? __('Jelajahi Ikan') : __('Beranda'))) }}
                </p>
                <a href="{{ route('profile.show') }}"
                    class="inline-flex min-h-11 max-w-40 items-center rounded-lg text-sm font-semibold text-anufish-navy hover:text-anufish-teal focus-visible:outline-2 focus-visible:outline-anufish-teal"><span
                        class="truncate">{{ auth()->user()->name }}</span></a>
                <x-language-switch />
            </header>
            <main id="main-content" tabindex="-1" class="min-h-0 min-w-0 flex-1 overflow-x-hidden overflow-y-auto">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:p-8">@yield('page-content')</div>
            </main>
        </div>
    </div>
@endsection
