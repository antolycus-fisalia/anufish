@props([
    'query' => null,
])

<form action="{{ route('homepage') }}" method="GET" class="mb-6 rounded-2xl border border-anufish-border bg-white p-4"
    data-submit-form="loading">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
        <div class="flex-1">
            <x-ui.form-field :label="__('Cari ikan')" name="q" :value="$query" :placeholder="__('Tuna, 鯛, atau Thunnus albacares')" autocomplete="off"
                required minlength="1" maxlength="150" dir="auto" spellcheck="false"
                aria-describedby="fish-search-help" />
        </div>

        <x-ui.button type="submit" class="w-full sm:w-30" data-submit-button>
            <span data-form-state="idle">
                {{ __('Cari') }}
            </span>

            <span hidden data-form-state="loading">
                {{ __('Mencari...') }}
            </span>
        </x-ui.button>
    </div>
    <p id="fish-search-help" class="mt-3 text-sm leading-[22px] text-anufish-muted">
        {{ __('Cari nama umum dalam berbagai bahasa atau nama ilmiah yang tercatat di iNaturalist.') }}</p>
</form>
