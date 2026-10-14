<div
    hidden
    data-form-state="loading"
    class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3"
    aria-live="polite"
>
    @for ($i = 0; $i < 6; $i++)
        <div
            class="animate-pulse overflow-hidden rounded-2xl border border-anufish-border bg-white p-4"
        >
            <div class="h-44 rounded-lg bg-anufish-pale"></div>

            <div class="p-4">
                <div class="h-5 w-2/3 rounded bg-gray-200"></div>
                <div class="mt-2 h-4 w-1/2 rounded bg-gray-100"></div>
            </div>
        </div>
    @endfor
</div>
