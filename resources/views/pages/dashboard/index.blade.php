@extends('layouts.authenticated')

@section('title', 'Dashboard - Anufish')

@section('page-content')
    <div class="mb-6">
        <h1 class="text-3xl font-bold tracking-tight text-anufish-navy">
            Jelajahi Ikan
        </h1>

        <p class="mt-2 text-sm leading-6 text-anufish-muted">
            Cari dan temukan informasi berbagai jenis ikan.
        </p>
    </div>

    @include('pages.dashboard.partials.fish-search')
@endsection
