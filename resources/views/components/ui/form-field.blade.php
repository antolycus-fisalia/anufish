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
    'disabled' => false,
])

@php
    $fieldId = $id ?? $name;
    $hasError = $errors->has($name);
    $descriptionIds = trim(($attributes->get('aria-describedby') ?? '') . ' ' . ($hasError ? $fieldId . '-error' : ''));
@endphp

<div>
    <label for="{{ $fieldId }}" class="mb-2 block text-sm font-semibold text-anufish-navy">
        {{ $label }}
    </label>

    <input id="{{ $fieldId }}" type="{{ $type }}" name="{{ $name }}" value="{{ $value }}"
        @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        @if ($required) required @endif @if ($autofocus) autofocus @endif
        @if ($disabled) disabled @endif aria-invalid="{{ $hasError ? 'true' : 'false' }}"
        @if ($descriptionIds !== '') aria-describedby="{{ $descriptionIds }}" @endif
        {{ $attributes->except('aria-describedby')->class([
                'block min-h-11 w-full rounded-lg border bg-white px-3 py-2.5 text-base leading-[22px] text-anufish-navy outline-none transition-colors placeholder:text-anufish-muted sm:text-sm disabled:cursor-not-allowed disabled:bg-gray-100',
                'border-rose-300 ring-1 ring-rose-100 focus:border-rose-500 focus:ring-4 focus:ring-rose-100' => $hasError,
                'border-anufish-border hover:border-anufish-cyan focus:border-anufish-teal focus:ring-4 focus:ring-anufish-pale' => !$hasError,
            ]) }}>

    <x-ui.validation-error :name="$name" :id="$fieldId" />
</div>
