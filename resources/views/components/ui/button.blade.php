@props([
    'variant' => 'primary',
    'type' => 'button',
])

@php
    $classes = match ($variant) {
        'primary' =>
            'bg-gradient-to-br from-anufish-blue to-anufish-cyan text-white',

        'secondary' =>
            'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50',

        'danger' =>
            'bg-red-600 text-white hover:bg-red-700',

        default =>
            'bg-slate-200 text-slate-700',
    };
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->class([
        'rounded-xl px-4 py-3 font-semibold transition',
        $classes,
    ]) }}
>
    {{ $slot }}
</button>