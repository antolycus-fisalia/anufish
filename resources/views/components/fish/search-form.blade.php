@props([
    'query' => null,
])

<form
    action="{{ route('homepage') }}"
    method="GET"
    class="mb-8"
    @submit="startSearch()"
>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
        <div class="flex-1">
            <x-ui.form-field
                label="Cari ikan"
                name="q"
                :value="$query"
                placeholder="Contoh: Tuna, Salmon, Clownfish"
                autocomplete="off"
            />
        </div>

        <x-ui.button
            type="submit"
            class="w-full sm:w-auto"
            x-bind:disabled="loading"
        >
            <span x-show="!loading">
                Cari
            </span>

            <span
                x-cloak
                x-show="loading"
            >
                Mencari...
            </span>
        </x-ui.button>
    </div>
</form>
