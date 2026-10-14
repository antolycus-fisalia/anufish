@props([
    'type' => 'button',
])

<button type="{{ $type }}" {{ $attributes->class('primary-action') }}>
    {{ $slot }}
</button>
