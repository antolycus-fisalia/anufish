@extends('layouts.authenticated')
@section('title', __('Detail Ikan - Anufish'))

@section('page-content')
    <a href="{{ $returnUrl }}"
        class="mb-4 inline-flex min-h-11 items-center gap-2 rounded-lg text-sm font-semibold text-anufish-teal hover:text-anufish-navy focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-anufish-teal"><span
            aria-hidden="true">←</span>{{ $returnLabel }}</a>
    @if (!$fish)
        <section class="rounded-2xl border border-anufish-border bg-white p-6"
            @if ($status !== 404) role="alert" @endif>
            <h1 class="page-title">{{ __($status === 404 ? 'Ikan tidak ditemukan' : 'Detail ikan belum dapat dimuat') }}</h1>
            <p class="mt-3 text-sm leading-[22px] text-anufish-muted">
                {{ __($status === 404 ? 'Spesies ikan ini tidak tersedia di iNaturalist. Kembali ke pencarian untuk memilih ikan lain.' : ($status === 429 ? 'Layanan iNaturalist sedang sibuk. Tunggu beberapa saat lalu coba lagi.' : 'Layanan iNaturalist sedang tidak tersedia. Silakan coba kembali beberapa saat lagi.')) }}
            </p>
            @if ($status !== 404)
                <a href="{{ request()->fullUrl() }}" class="primary-action mt-6">{{ __('Coba lagi') }}</a>
            @endif
        </section>
    @else
        @php($displayName = $fish['common_name'] ?? $fish['scientific_name'])
        <div data-fish-detail="{{ json_encode(['image' => $fish['image'], 'description' => $fish['description'], 'descriptionLanguage' => $fish['description_language'], 'needsExtras' => $fish['needs_extras'], 'endpoint' => route('fish.extras', ['taxonId' => $fish['taxon_id']])]) }}">
            <header class="mb-6">
                <p class="mb-2 text-sm font-medium text-anufish-teal">{{ __('Detail spesies ikan') }}</p>
                <h1 class="page-title break-words"><bdi>{{ $displayName }}</bdi></h1>
                <p class="mt-2 text-base italic leading-6 text-anufish-muted"><bdi>{{ $fish['scientific_name'] }}</bdi></p>
            </header>
            <div class="grid items-start gap-6 lg:grid-cols-2">
                <x-fish.photo-gallery :fish="$fish" />
                <section aria-labelledby="taxonomy-title"
                    class="min-w-0 rounded-2xl border border-anufish-border bg-white p-6">
                    <h2 id="taxonomy-title" class="text-2xl font-semibold leading-8 text-anufish-navy">
                        {{ __('Taksonomi') }}</h2>
                    <dl class="mt-5 grid grid-cols-2 gap-4 text-sm">
                        @foreach (['kingdom' => 'Kingdom', 'phylum' => 'Filum', 'class' => 'Kelas', 'order' => 'Ordo', 'family' => 'Famili', 'genus' => 'Genus', 'species' => 'Spesies'] as $key => $label)
                            <div class="min-w-0">
                                <dt class="text-xs text-anufish-muted">{{ __($label) }}</dt>
                                <dd class="mt-1 break-words font-medium leading-[22px] text-anufish-navy">
                                    <bdi>{{ $fish['taxonomy'][$key] ?? __('Belum tersedia') }}</bdi></dd>
                            </div>
                        @endforeach
                    </dl>
                    <a href="{{ $fish['source_url'] }}" target="_blank" rel="noopener noreferrer"
                        class="mt-6 inline-flex min-h-11 items-center gap-2 rounded-lg text-sm font-semibold text-anufish-teal hover:text-anufish-navy focus-visible:outline-2 focus-visible:outline-anufish-teal">{{ __('Lihat sumber iNaturalist') }}
                        <span aria-hidden="true">↗</span><span class="sr-only">{{ __(' (tab baru)') }}</span></a>
                </section>
            </div>
            <section aria-labelledby="description-title" class="mt-6 rounded-2xl border border-anufish-border bg-white p-6">
                <h2 id="description-title" class="text-2xl font-semibold leading-8 text-anufish-navy">
                    {{ __('Deskripsi spesies') }}</h2>
                <p data-detail-state="description" @if (!$fish['description']) hidden @endif data-detail-description lang="{{ $fish['description_language'] }}"
                    class="mt-4 max-w-3xl break-words text-sm leading-[22px] text-anufish-navy">{{ $fish['description'] }}
                </p>
                <p data-detail-state="description-loading" hidden role="status" class="mt-4 text-sm text-anufish-muted">
                    {{ __('Memuat deskripsi...') }}</p>
                <p data-detail-state="description-missing" @if ($fish['description']) hidden @endif class="mt-4 text-sm leading-[22px] text-anufish-muted">
                    {{ __('Deskripsi belum tersedia di iNaturalist.') }}</p>
                <p data-detail-state="description" @if (!$fish['description']) hidden @endif class="mt-3 text-xs leading-[18px] text-anufish-muted">
                    {{ __('Ringkasan Wikipedia melalui iNaturalist') }}</p>
                @if (app()->getLocale() === 'id')
                    <p data-detail-state="english-fallback" hidden class="mt-3 text-sm text-anufish-muted">
                        {{ __('Deskripsi Indonesia belum tersedia. Menampilkan versi Inggris.') }}</p>
                @endif
                <p data-detail-state="error" hidden role="status" class="mt-3 text-sm text-anufish-muted">
                    {{ __('Informasi tambahan belum dapat dimuat.') }} <button type="button" data-detail-retry
                        class="inline-flex min-h-11 items-center rounded-lg px-2 font-semibold text-anufish-teal">{{ __('Coba lagi') }}</button>
                </p>
            </section>
            <section aria-labelledby="names-title" class="mt-6 rounded-2xl border border-anufish-border bg-white p-6">
                <h2 id="names-title" class="text-2xl font-semibold leading-8 text-anufish-navy">
                    {{ __('Nama Indonesia dan Inggris') }}</h2>
                <ul class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @forelse($fish['vernacular_names'] as $name)
                        <x-fish.vernacular-name :name="$name" />
                    @empty<li class="text-sm text-anufish-muted">{{ __('Nama umum belum tersedia di iNaturalist.') }}
                        </li>
                    @endforelse
                </ul>
            </section>
        </div>
    @endif
@endsection
