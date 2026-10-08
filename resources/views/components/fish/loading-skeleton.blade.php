<div
    x-cloak
    x-show="loading"
    class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
    aria-live="polite"
>
    @for ($i = 0; $i < 8; $i++)
        <div
            class="animate-pulse overflow-hidden rounded-2xl border border-anufish-border bg-white shadow-sm"
        >
            <div class="aspect-[4/3] bg-gray-200"></div>

            <div class="p-4">
                <div class="h-5 w-2/3 rounded bg-gray-200"></div>
                <div class="mt-2 h-4 w-1/2 rounded bg-gray-100"></div>
            </div>
        </div>
    @endfor
</div>
