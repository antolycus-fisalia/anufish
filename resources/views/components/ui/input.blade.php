@props([
    'label' => null,
    'name',
    'type' => 'text',
])

<div>
    @if ($label)
        <label
            for="{{ $name }}"
            class="mb-2 block text-sm font-semibold text-anufish-text"
        >
            {{ $label }}
        </label>
    @endif

    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        @error($name)
            aria-invalid="true"
            aria-describedby="{{ $name }}-error"
        @enderror
        {{ $attributes->class([
            'w-full rounded-xl border bg-white px-4 py-3 text-anufish-text outline-none transition placeholder:text-slate-400 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-500 disabled:opacity-70',
            'border-slate-300 hover:border-slate-400 focus:border-anufish-cyan focus:ring-2 focus:ring-anufish-cyan/20' => ! $errors->has($name),
            'border-red-600 focus:border-red-600 focus:ring-2 focus:ring-red-600/20' => $errors->has($name),
        ]) }}
    >

    @error($name)
        <p
            id="{{ $name }}-error"
            class="mt-2 text-sm font-medium text-red-700"
            role="alert"
        >
            {{ $message }}
        </p>
    @enderror
</div>
