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
        {{-- Header halaman --}}
        <div class="mb-6">
            <h1 class="text-3xl font-bold tracking-tight text-anufish-navy">
                Jelajahi Ikan
            </h1>

            <p class="mt-2 text-sm leading-6 text-anufish-muted">
                Cari dan temukan informasi berbagai jenis ikan.
            </p>
        </div>

        {{-- Form pencarian --}}
        <form
            action="{{ route('homepage') }}"
            method="GET"
            class="mb-8"
            @submit="startSearch()"
        >
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="flex-1">
                    <x-ui.form-field
                        label="Cari ikan"
                        name="q"
                        :value="$query"
                        placeholder="Contoh: Tuna, Salmon, Clownfish"
                        autocomplete="off"
                    />
                </div>

                <x-ui.button
                    type="submit"
                    class="w-full sm:w-auto"
                    x-bind:disabled="loading"
                >
                    <span x-show="!loading">
                        Cari
                    </span>

                    <span
                        x-cloak
                        x-show="loading"
                    >
                        Mencari...
                    </span>
                </x-ui.button>
            </div>
        </form>

        {{-- Loading state --}}
        <div
            x-cloak
            x-show="loading"
            class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
            aria-live="polite"
        >
            @for ($i = 0; $i < 8; $i++)
                <div
                    class="animate-pulse overflow-hidden rounded-2xl border border-anufish-border bg-white shadow-sm"
                >
                    <div class="aspect-[4/3] bg-gray-200"></div>

                    <div class="p-4">
                        <div class="h-5 w-2/3 rounded bg-gray-200"></div>

                        <div class="mt-2 h-4 w-1/2 rounded bg-gray-100"></div>
                    </div>
                </div>
            @endfor
        </div>

        {{-- Konten setelah loading --}}
        <div x-show="!loading">

            {{-- Failure state --}}
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

            {{-- Hasil pencarian --}}
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

                {{-- Grid ikan --}}
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($fishes as $fish)
                        <article
                            class="overflow-hidden rounded-2xl border border-anufish-border bg-white shadow-sm transition hover:border-anufish-cyan"
                        >
                            {{-- Gambar ikan --}}
                            <div class="aspect-[4/3] bg-anufish-pale">
                                @if (!empty($fish['image']))
                                    <img
                                        src="{{ $fish['image'] }}"
                                        alt="{{ $fish['common_name'] ?? $fish['scientific_name'] ?? 'Gambar ikan' }}"
                                        class="size-full object-cover"
                                        loading="lazy"
                                    >
                                @else
                                    <div
                                        class="flex size-full items-center justify-center px-4 text-center text-sm text-anufish-muted"
                                    >
                                        Gambar tidak tersedia
                                    </div>
                                @endif
                            </div>

                            {{-- Informasi ikan --}}
                            <div class="p-4">
                                <h3 class="font-bold text-anufish-navy">
                                    {{ $fish['common_name'] ?? 'Nama umum tidak tersedia' }}
                                </h3>

                                <p class="mt-1 text-sm italic text-anufish-muted">
                                    {{ $fish['scientific_name'] ?? 'Nama ilmiah tidak tersedia' }}
                                </p>
                            </div>
                        </article>
                    @endforeach
                </div>

                {{-- Pagination --}}
                @if ($pagination)
                    <nav
                        class="mt-8 flex flex-col items-center justify-between gap-4 border-t border-anufish-border pt-6 sm:flex-row"
                        aria-label="Navigasi halaman hasil pencarian"
                    >
                        {{-- Previous --}}
                        @if ($pagination['has_previous'] ?? false)
                            <a
                                href="{{ route('homepage', [
                                    'q' => $query,
                                    'page' => $pagination['previous_page'],
                                ]) }}"
                                @click="startSearch()"
                                class="inline-flex w-full items-center justify-center rounded-lg border border-anufish-border bg-white px-4 py-2.5 text-sm font-semibold text-anufish-navy transition hover:border-anufish-cyan hover:bg-anufish-pale focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-anufish-teal focus-visible:ring-offset-2 sm:w-auto"
                            >
                                Sebelumnya
                            </a>
                        @else
                            <span
                                class="inline-flex w-full cursor-not-allowed items-center justify-center rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-semibold text-gray-400 sm:w-auto"
                            >
                                Sebelumnya
                            </span>
                        @endif

                        {{-- Nomor halaman --}}
                        <p class="text-sm text-anufish-muted">
                            Halaman

                            <span class="font-semibold text-anufish-navy">
                                {{ $pagination['current_page'] ?? 1 }}
                            </span>

                            @if (!empty($pagination['total_pages']))
                                dari

                                <span class="font-semibold text-anufish-navy">
                                    {{ $pagination['total_pages'] }}
                                </span>
                            @endif
                        </p>

                        {{-- Next --}}
                        @if ($pagination['has_next'] ?? false)
                            <a
                                href="{{ route('homepage', [
                                    'q' => $query,
                                    'page' => $pagination['next_page'],
                                ]) }}"
                                @click="startSearch()"
                                class="inline-flex w-full items-center justify-center rounded-lg border border-anufish-border bg-white px-4 py-2.5 text-sm font-semibold text-anufish-navy transition hover:border-anufish-cyan hover:bg-anufish-pale focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-anufish-teal focus-visible:ring-offset-2 sm:w-auto"
                            >
                                Berikutnya
                            </a>
                        @else
                            <span
                                class="inline-flex w-full cursor-not-allowed items-center justify-center rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-semibold text-gray-400 sm:w-auto"
                            >
                                Berikutnya
                            </span>
                        @endif
                    </nav>
                @endif

            {{-- Empty state setelah pencarian --}}
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

            {{-- Initial empty state --}}
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