@extends('layouts.authenticated')

@section('title', 'Beranda - Anufish')

@section('page-content')
    @php
        $fishes = $fishes ?? [];
        $error = $error ?? null;
        $pagination = $pagination ?? null;
        $query = request('q');
    @endphp

    <div x-data="fishSearch">
        <div class="mb-6">
            <h1 class="text-3xl font-bold tracking-tight text-anufish-navy">
                Jelajahi Ikan
            </h1>

            <p class="mt-2 text-sm leading-6 text-anufish-muted">
                Cari dan temukan informasi berbagai jenis ikan.
            </p>
        </div>

        <x-fish.search-form :query="$query" />

        <x-fish.loading-skeleton />

        <div x-show="!loading">
            @if ($error)
                <div
                    class="rounded-2xl border border-rose-200 bg-rose-50 px-6 py-5"
                    role="alert"
                >
                    <h2 class="font-bold text-rose-700">
                        Gagal mengambil data ikan
                    </h2>

                    <p class="mt-1 text-sm leading-6 text-rose-600">
                        {{ $error }}
                    </p>
                </div>

            @elseif (count($fishes) > 0)
                <div class="mb-4">
                    <h2 class="text-lg font-bold text-anufish-navy">
                        Hasil Pencarian
                    </h2>

                    @if ($query)
                        <p class="mt-1 text-sm text-anufish-muted">
                            Hasil untuk

                            <span class="font-semibold text-anufish-navy">
                                “{{ $query }}”
                            </span>
                        </p>
                    @endif
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($fishes as $fish)
                        <x-fish.fish-card :fish="$fish" />
                    @endforeach
                </div>

                @if ($pagination)
                    <x-fish.pagination
                        :pagination="$pagination"
                        :query="$query"
                    />
                @endif

            @elseif ($query)
                <div
                    class="rounded-2xl border border-anufish-border bg-white px-6 py-12 text-center"
                >
                    <h2 class="text-lg font-bold text-anufish-navy">
                        Ikan tidak ditemukan
                    </h2>

                    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-anufish-muted">
                        Tidak ada hasil untuk “{{ $query }}”.
                        Coba gunakan nama ikan atau kata kunci lainnya.
                    </p>
                </div>

            @else
                <div
                    class="rounded-2xl border border-anufish-border bg-white px-6 py-12 text-center"
                >
                    <h2 class="text-lg font-bold text-anufish-navy">
                        Cari ikan favorit Anda
                    </h2>

                    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-anufish-muted">
                        Masukkan nama ikan pada kolom pencarian untuk melihat informasi yang tersedia.
                    </p>
                </div>
            @endif
        </div>
    </div>
@endsection
