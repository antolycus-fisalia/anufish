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

<div x-data="{ showPassword: false }">
    <label for="{{ $fieldId }}" class="mb-2 block text-sm font-semibold text-anufish-navy">
        {{ $label }}
    </label>

    <div class="relative">
        <input id="{{ $fieldId }}" type="password" x-bind:type="showPassword ? 'text' : 'password'"
            name="{{ $name }}"
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            @if ($required) required @endif
            @if ($disabled) disabled @endif
            aria-invalid="{{ $hasError ? 'true' : 'false' }}"
            @if ($hasError) aria-describedby="{{ $fieldId }}-error" @endif
            {{ $attributes->class([
                'block w-full rounded-lg border bg-white px-4 py-3 pr-12 text-base text-anufish-navy shadow-sm outline-none transition placeholder:text-anufish-muted sm:text-sm disabled:cursor-not-allowed disabled:bg-gray-100 disabled:opacity-60',
                'border-rose-300 ring-1 ring-rose-100 focus:border-rose-500 focus:ring-4 focus:ring-rose-100' => $hasError,
                'border-anufish-border hover:border-anufish-cyan focus:border-anufish-teal focus:ring-4 focus:ring-anufish-pale' => ! $hasError,
            ]) }}>

        <button type="button" aria-controls="{{ $fieldId }}" aria-label="Tampilkan password" aria-pressed="false"
            x-bind:aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
            x-bind:aria-pressed="showPassword.toString()" x-on:click="showPassword = ! showPassword"
            class="absolute inset-y-1.5 right-1.5 flex w-10 items-center justify-center rounded-md text-anufish-muted transition hover:bg-anufish-pale hover:text-anufish-navy focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-anufish-teal focus-visible:ring-offset-2">
            <svg x-show="! showPassword" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="1.75" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M2.04 12.32a1 1 0 0 1 0-.64C3.42 7.51 7.35 4.5 12 4.5c4.65 0 8.58 3.01 9.96 7.18a1 1 0 0 1 0 .64C20.58 16.49 16.65 19.5 12 19.5c-4.65 0-8.58-3.01-9.96-7.18Z" />
                <circle cx="12" cy="12" r="3" />
            </svg>

            <svg x-cloak x-show="showPassword" style="display: none;" class="size-5" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="m3 3 18 18M10.58 10.59a2 2 0 0 0 2.83 2.83M9.88 4.83A10.8 10.8 0 0 1 12 4.62c4.65 0 8.58 3 9.96 7.17a1 1 0 0 1 0 .64 11.06 11.06 0 0 1-2.16 3.72M6.61 6.61a11 11 0 0 0-4.57 5.18 1 1 0 0 0 0 .64C3.42 16.6 7.35 19.62 12 19.62a10.8 10.8 0 0 0 3.38-.54" />
            </svg>
        </button>
    </div>

    <x-ui.validation-error :name="$name" :id="$fieldId" />
</div>
