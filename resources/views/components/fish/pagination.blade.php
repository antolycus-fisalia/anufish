@props(['pagination', 'query'])

@if ($pagination['current_page'] > 1 || $pagination['has_more'])
    <nav aria-label="{{ __('Navigasi hasil pencarian') }}" class="mt-6 flex flex-wrap items-center gap-3">
        @if ($pagination['current_page'] > 1)
            <a href="{{ route('homepage', ['q' => $query, 'page' => $pagination['current_page'] - 1]) }}"
                data-search-link
                class="inline-flex min-h-11 items-center rounded-lg border border-anufish-border bg-white px-4 py-2 text-sm font-semibold text-anufish-navy hover:bg-anufish-pale focus-visible:outline-2 focus-visible:outline-anufish-teal">
                {{ __('Sebelumnya') }}
            </a>
        @else
            <span></span>
        @endif

        <span aria-current="page" class="text-sm text-anufish-muted">{{ __('Halaman') }} {{ $pagination['current_page'] }}</span>

        @if ($pagination['has_more'])
            <a href="{{ route('homepage', ['q' => $query, 'page' => $pagination['current_page'] + 1]) }}"
                data-search-link
                class="inline-flex min-h-11 items-center rounded-lg border border-anufish-border bg-white px-4 py-2 text-sm font-semibold text-anufish-navy hover:bg-anufish-pale focus-visible:outline-2 focus-visible:outline-anufish-teal">
                {{ __('Selanjutnya') }}
            </a>
        @else
            <span></span>
        @endif
    </nav>
@endif
