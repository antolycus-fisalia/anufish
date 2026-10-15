@props(['fish'])

@php
    $names = $fish['vernacular_names'] ?? [];
    $displayName =
        $fish['common_name'] ?? ($fish['canonical_name'] ?? ($fish['scientific_name'] ?? __('Nama tidak tersedia')));
@endphp

<article class="min-w-0 rounded-2xl border border-anufish-border bg-white p-5">
    <div class="fish-card-media" data-fish-card-media>
        <div class="fish-card-placeholder" data-fish-card-placeholder @if ($fish['image'] ?? null) hidden @endif>
            <x-navigation.icon name="fish" />
            <span>{{ __('Gambar belum tersedia') }}</span>
        </div>
        @if ($fish['image'] ?? null)
            <img src="{{ $fish['image']['url'] }}" alt="{{ $displayName }}" width="416" height="260" loading="lazy" decoding="async" referrerpolicy="no-referrer" class="relative bg-anufish-pale" data-fish-card-image>
        @endif
    </div>
    @if ($fish['image'] ?? null)
        <p class="fish-card-credit">{{ $fish['image']['credit'] }} · <a href="{{ $fish['image']['source_url'] }}" target="_blank" rel="noopener noreferrer">iNaturalist<span class="sr-only">{{ __(' (tab baru)') }}</span></a> · <a href="{{ $fish['image']['license']['url'] }}" target="_blank" rel="noopener noreferrer">{{ $fish['image']['license']['label'] }}<span class="sr-only">{{ __(' (tab baru)') }}</span></a></p>
    @endif
    <header class="mb-5 flex items-start gap-3">
        <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-anufish-pale text-anufish-teal"
            aria-hidden="true"><x-navigation.icon name="fish" /></span>
        <div class="min-w-0">
            <p class="mb-1 text-xs font-medium text-anufish-muted">{{ __('Spesies ikan') }}</p>
            <h3 class="break-words text-lg font-semibold leading-[26px] text-anufish-navy"><bdi
                    @if ($fish['common_name_language'] ?? null) lang="{{ $fish['common_name_language'] }}" @endif>{{ $displayName }}</bdi>
            </h3>
        </div>
    </header>

    <p class="break-words text-sm italic leading-[22px] text-anufish-muted"><bdi
            dir="ltr">{{ $fish['scientific_name'] ?? ($fish['canonical_name'] ?? __('Nama ilmiah belum tersedia')) }}</bdi>
    </p>
    @if ($fish['common_name_language_label'] ?? null)
        <p class="mt-2 inline-flex rounded-lg bg-anufish-pale px-2 py-1 text-xs font-medium text-anufish-teal">
            {{ $fish['common_name_language_label'] }}</p>
    @endif

    <dl class="mt-5 grid grid-cols-2 gap-4 border-t border-anufish-border/50 pt-4 text-sm">
        @foreach (['class' => __('Kelas'), 'order' => __('Ordo'), 'family' => __('Famili'), 'genus' => 'Genus'] as $key => $label)
            <div class="min-w-0">
                <dt class="text-xs text-anufish-muted">{{ $label }}</dt>
                <dd class="mt-1 break-words font-medium leading-[22px] text-anufish-navy">
                    <bdi>{{ $fish['taxonomy'][$key] ?? __('Belum tersedia') }}</bdi>
                </dd>
            </div>
        @endforeach
    </dl>

    @if (count($names) > 0)
        <details class="group mt-5 border-t border-anufish-border/50 pt-2">
            <summary
                class="flex min-h-11 cursor-pointer items-center justify-between gap-2 rounded-lg px-1 text-sm font-semibold text-anufish-teal hover:bg-anufish-pale focus-visible:outline-2 focus-visible:outline-anufish-teal">
                {{ __('Nama Indonesia dan Inggris') }} <span class="shrink-0 text-xs font-medium">{{ count($names) }}
                    {{ __('nama') }} <span aria-hidden="true" class="group-open:hidden">+</span><span
                        aria-hidden="true" class="hidden group-open:inline">−</span></span>
            </summary>
            <ul aria-label="{{ __('Nama umum dalam berbagai bahasa') }}"
                class="mt-2 max-h-64 space-y-3 overflow-y-auto rounded-lg bg-anufish-pale p-3">
                @foreach ($names as $name)
                    <li class="min-w-0">
                        <span class="block text-xs text-anufish-muted">{{ $name['language_label'] }}</span>
                        <bdi class="mt-1 block break-words text-sm leading-[22px] text-anufish-navy"
                            @if ($name['language']) lang="{{ $name['language'] }}" @endif>{{ $name['name'] }}</bdi>
                    </li>
                @endforeach
            </ul>
        </details>
    @else
        <p class="mt-5 border-t border-anufish-border/50 pt-4 text-sm leading-[22px] text-anufish-muted">
            {{ __('Nama umum belum tersedia di iNaturalist.') }}</p>
    @endif

    @if (is_numeric($fish['taxon_id'] ?? null) && (int) $fish['taxon_id'] > 0)
        <a href="{{ route('fish.show', ['taxonId' => (int) $fish['taxon_id']] + request()->only(['q', 'page'])) }}"
            class="primary-action mt-4 w-full">{{ __('Lihat detail ikan') }}<span class="sr-only">:
                {{ $displayName }}</span></a>
    @endif
    @if ($fish['source_url'] ?? null)
        <a href="{{ $fish['source_url'] }}" target="_blank" rel="noopener noreferrer"
            class="mt-3 flex min-h-11 items-center justify-center rounded-lg border border-anufish-border px-3 text-sm font-semibold text-anufish-navy hover:bg-anufish-pale focus-visible:outline-2 focus-visible:outline-anufish-teal">{{ __('Lihat sumber iNaturalist') }}
            <span class="ml-2" aria-hidden="true">↗</span><span class="sr-only">{{ __(' (tab baru)') }}</span></a>
    @endif
</article>
