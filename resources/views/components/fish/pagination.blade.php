@props(['pagination', 'query'])

@if ($pagination['current_page'] > 1 || $pagination['has_more'])
    <nav aria-label="Navigasi hasil pencarian" class="mt-6 flex items-center justify-between gap-3">
        @if ($pagination['current_page'] > 1)
            <a href="{{ route('homepage', ['q' => $query, 'page' => $pagination['current_page'] - 1]) }}"
                @click="startSearch()"
                class="rounded-lg border border-anufish-border bg-white px-4 py-2 text-sm font-semibold text-anufish-navy">
                Sebelumnya
            </a>
        @else
            <span></span>
        @endif

        <span aria-current="page" class="text-sm text-anufish-muted">Halaman {{ $pagination['current_page'] }}</span>

        @if ($pagination['has_more'])
            <a href="{{ route('homepage', ['q' => $query, 'page' => $pagination['current_page'] + 1]) }}"
                @click="startSearch()"
                class="rounded-lg border border-anufish-border bg-white px-4 py-2 text-sm font-semibold text-anufish-navy">
                Selanjutnya
            </a>
        @else
            <span></span>
        @endif
    </nav>
@endif
