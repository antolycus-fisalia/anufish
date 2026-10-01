@props([
    'label',
    'name',
    'type' => 'text',
    'id' => null,
    'value' => null,
    'placeholder' => null,
    'autocomplete' => null,
    'required' => false,
    'autofocus' => false,
])

@php
    $fieldId = $id ?? $name;
    $hasError = $errors->has($name);
@endphp

<div>
    <label for="{{ $fieldId }}" class="mb-2 block text-sm font-semibold text-anufish-navy">
        {{ $label }}
    </label>

    <input id="{{ $fieldId }}" type="{{ $type }}" name="{{ $name }}" value="{{ $value }}"
        @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        @if ($required) required @endif
        @if ($autofocus) autofocus @endif
        aria-invalid="{{ $hasError ? 'true' : 'false' }}"
        @if ($hasError) aria-describedby="{{ $fieldId }}-error" @endif
        {{ $attributes->class([
            'block w-full rounded-lg border bg-white px-4 py-3 text-base text-anufish-navy shadow-sm outline-none transition placeholder:text-anufish-muted sm:text-sm',
            'border-rose-300 ring-1 ring-rose-100 focus:border-rose-500 focus:ring-4 focus:ring-rose-100' => $hasError,
            'border-anufish-border hover:border-anufish-cyan focus:border-anufish-teal focus:ring-4 focus:ring-anufish-pale' => ! $hasError,
        ]) }}>

    @error($name)
        <p id="{{ $fieldId }}-error" class="mt-2 text-sm font-medium text-rose-600" role="alert">
            {{ $message }}
        </p>
    @enderror
</div>
