@props([
    'variant' => 'primary',
    'type' => 'button',
])

@php
    $classes = match ($variant) {
        'primary' =>
            'bg-gradient-to-br from-anufish-blue to-cyan-700 text-white hover:from-sky-700 hover:to-cyan-800',

        'secondary' =>
            'border border-slate-300 bg-white text-slate-700 hover:border-slate-400 hover:bg-slate-50',

        'danger' =>
            'bg-red-600 text-white hover:bg-red-700',

        default =>
            'bg-slate-200 text-slate-700',
    };
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->class([
        'rounded-xl px-4 py-3 font-semibold transition active:scale-95 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-anufish-cyan focus-visible:ring-offset-2 disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50',
        $classes,
    ]) }}
>
    {{ $slot }}
</button>
