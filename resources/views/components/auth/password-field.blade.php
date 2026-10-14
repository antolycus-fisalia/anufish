@props([
    'label',
    'name' => 'password',
    'id' => null,
    'placeholder' => null,
    'autocomplete' => null,
    'required' => false,
    'disabled' => false,
])

@php
    $fieldId = $id ?? $name;
    $hasError = $errors->has($name);
@endphp

<div data-password-field>
    <label for="{{ $fieldId }}" class="mb-2 block text-sm font-semibold text-anufish-navy">
        {{ $label }}
    </label>

    <div class="relative">
        <input id="{{ $fieldId }}" type="password"
            name="{{ $name }}"
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            @if ($required) required @endif
            @if ($disabled) disabled @endif
            aria-invalid="{{ $hasError ? 'true' : 'false' }}"
            @if ($hasError) aria-describedby="{{ $fieldId }}-error" @endif
            {{ $attributes->class([
                'password-input block min-h-11 w-full rounded-lg border bg-white py-2.5 text-base leading-[22px] text-anufish-navy outline-none transition-colors placeholder:text-anufish-muted sm:text-sm disabled:cursor-not-allowed disabled:bg-gray-100 disabled:opacity-60',
                'border-rose-300 ring-1 ring-rose-100 focus:border-rose-500 focus:ring-4 focus:ring-rose-100' => $hasError,
                'border-anufish-border hover:border-anufish-cyan focus:border-anufish-teal focus:ring-4 focus:ring-anufish-pale' => ! $hasError,
            ]) }}>

        <button type="button" aria-controls="{{ $fieldId }}" aria-label="{{ __('Tampilkan password') }}" aria-pressed="false"
            data-password-toggle data-show-label="{{ __('Tampilkan password') }}" data-hide-label="{{ __('Sembunyikan password') }}"
            @if ($disabled) disabled @endif class="password-toggle">
            <span data-password-eye><x-navigation.icon name="eye" /></span>
            <span data-password-eye-off hidden><x-navigation.icon name="eye-off" /></span>
        </button>
    </div>

    <x-ui.validation-error :name="$name" :id="$fieldId" />
</div>
