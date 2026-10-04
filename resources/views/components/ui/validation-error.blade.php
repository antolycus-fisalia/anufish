@props([
    'name',
    'id' => null,
])

@error($name)
    <p
        id="{{ ($id ?? $name) }}-error"
        class="mt-2 text-sm font-medium text-rose-600"
        role="alert"
    >
        {{ $message }}
    </p>
@enderror