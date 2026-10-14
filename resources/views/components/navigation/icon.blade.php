@props(['name'])
@php
    $paths = [
        'home' => 'm3 10 9-7 9 7v11h-6v-7H9v7H3Z',
        'fish' => 'M6 12c3-7 10-7 14 0-4 7-11 7-14 0Zm0 0-4-4v8l4-4M15 11h.01',
        'article' => 'M5 3h14v18H5zM8 7h8M8 11h8M8 15h5',
        'grid' => 'M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z',
        'scan' => 'M7 3H3v4M17 3h4v4M21 17v4h-4M7 21H3v-4M7 12h10',
        'history' => 'M3 11a9 9 0 1 1 2 7M3 4v7h7M12 7v5l3 2',
        'edit' => 'm15 4 5 5M4 20l4-1L20 7a2 2 0 0 0-4-4L4 15z',
        'logout' => 'M9 3H4v18h5M10 12h11m-4-4 4 4-4 4',
        'menu' => 'M4 6h16M4 12h16M4 18h16',
        'close' => 'm6 6 12 12M6 18 18 6',
        'expand' => 'M8 3H3v5M16 3h5v5M21 16v5h-5M3 16v5h5',
        'chevron-left' => 'm14 6-6 6 6 6',
        'chevron-right' => 'm10 6 6 6-6 6',
        'eye' => 'M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7ZM15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
        'eye-off' => 'm3 3 18 18M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7ZM15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
    ];
@endphp
<svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    <path d="{{ $paths[$name] }}" />
</svg>
