@props([
    'label' => null,
    'name',
    'type' => 'text',
])

<div>
    @if ($label)
        <label
            for="{{ $name }}"
            class="mb-2 block text-sm font-semibold"
        >
            {{ $label }}
        </label>
    @endif

    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        {{ $attributes->class([
            'w-full rounded-xl border border-slate-200 px-4 py-3 outline-none transition focus:border-anufish-cyan focus:ring-2 focus:ring-anufish-cyan/20'
        ]) }}
    >

    @error($name)
        <p class="mt-1 text-sm text-red-600">
            {{ $message }}
        </p>
    @enderror
</div>