@php
    $fishes = $fishes ?? [];
    $error = $error ?? null;
    $pagination = $pagination ?? null;
    $query = request('q');
    $hasQuery = request()->filled('q');
@endphp

<div data-search-scope>
    <x-fish.search-form :query="$query" />

    <x-fish.loading-skeleton />

    <div data-form-state="idle">
        @if ($error)
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-6 py-5" role="alert">
                <h2 class="font-bold text-rose-700">
                    {{ __('Gagal mengambil data ikan') }}
                </h2>

                <p class="mt-1 text-sm leading-6 text-rose-600">
                    {{ $error }}
                </p>
                <a href="{{ request()->fullUrl() }}" class="primary-action mt-4">{{ __('Coba lagi') }}</a>
            </div>
        @elseif (count($fishes) > 0)
            <div class="mb-4">
                <h2 class="text-lg font-bold text-anufish-navy">
                    {{ __('Hasil Pencarian') }}
                </h2>
                <p class="mt-2 text-sm text-anufish-muted">{{ count($fishes) }}
                    {{ __('spesies ditampilkan · Sumber: iNaturalist') }}</p>

                @if ($hasQuery)
                    <p class="mt-1 text-sm text-anufish-muted">
                        {{ __('Hasil untuk') }}

                        <span class="font-semibold text-anufish-navy">
                            “<bdi>{{ $query }}</bdi>”
                        </span>
                    </p>
                @endif
            </div>

            <div class="grid items-start gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($fishes as $fish)
                    <x-fish.fish-card :fish="$fish" />
                @endforeach
            </div>

            <aside
                class="mt-6 rounded-2xl border border-anufish-border bg-anufish-pale p-4 text-sm leading-[22px] text-anufish-teal">
                <h3 class="font-semibold">{{ __('Data tidak selalu lengkap') }}</h3>
                <p class="mt-2">
                    {{ __('Nama dan bahasa mengikuti data iNaturalist. Sebagian spesies belum memiliki nama umum atau taksonomi yang lengkap.') }}
                </p>
            </aside>
        @elseif ($hasQuery)
            <div class="rounded-2xl border border-anufish-border bg-white px-6 py-12 text-center">
                <h2 class="text-lg font-bold text-anufish-navy">
                    {{ __('Ikan tidak ditemukan') }}
                </h2>

                <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-anufish-muted">
                    {{ __('Tidak ada hasil di halaman ini untuk') }} “<bdi>{{ $query }}</bdi>”.
                    {{ __('Coba nama ilmiah, nama dalam bahasa lain, atau halaman berikutnya jika tersedia.') }}
                </p>
            </div>
        @else
            <div class="rounded-2xl border border-anufish-border bg-white px-6 py-12 text-center">
                <h2 class="text-lg font-bold text-anufish-navy">
                    {{ __('Cari ikan favorit Anda') }}
                </h2>

                <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-anufish-muted">
                    {{ __('Masukkan nama ikan pada kolom pencarian untuk melihat informasi yang tersedia.') }}
                </p>
            </div>
        @endif

        @if ($pagination)
            <x-fish.pagination :pagination="$pagination" :query="$query" />
        @endif
    </div>
</div>
