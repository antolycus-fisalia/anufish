@extends('layouts.authenticated')
@section('title', __('Jelajahi Ikan - Anufish'))

@section('page-content')
    <header class="mb-6">
        <h1 class="page-title">{{ __('Jelajahi Ikan') }}</h1>
        <p class="mt-3 text-base leading-6 text-anufish-muted">{{ __('Cari spesies berdasarkan nama umum atau nama ilmiahnya.') }}</p>
    </header>
    <section id="fish-search" aria-label="{{ __('Pencarian ikan') }}">
        @include('pages.dashboard.partials.fish-search')
    </section>
@endsection
