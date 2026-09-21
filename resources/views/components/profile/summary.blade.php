@props([
    'user',
])

<x-ui.card>
    <div class="flex items-center gap-4">

        <div
            class="
                grid
                h-20
                w-20
                place-items-center
                overflow-hidden
                rounded-2xl
                bg-gradient-to-br
                from-anufish-blue
                to-anufish-cyan
                text-xl
                font-bold
                text-white
            "
        >
            @if ($user->photo)
                <img
                    src="{{ $user->photo }}"
                    alt="{{ $user->name }}"
                    class="h-full w-full object-cover"
                >
            @else
                {{ collect(explode(' ', $user->name))
                    ->map(fn ($word) => strtoupper(substr($word, 0, 1)))
                    ->take(2)
                    ->join('') }}
            @endif
        </div>

        <div>
            <h3 class="text-xl font-bold">
                {{ $user->name }}
            </h3>

            <p class="text-anufish-muted">
                {{ $user->email }}
            </p>

            <span
                class="
                    mt-2
                    inline-block
                    rounded-full
                    bg-emerald-50
                    px-3
                    py-1
                    text-xs
                    font-semibold
                    text-emerald-700
                "
            >
                {{ $user->status }}
            </span>
        </div>

    </div>

    <div class="mt-6 grid gap-3 sm:grid-cols-3">

        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
            <strong class="block text-xl">
                {{ $user->scan_count }}
            </strong>

            <span class="text-sm text-anufish-muted">
                Riwayat Scan
            </span>
        </div>

        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
            <strong class="block text-xl">
                {{ $user->article_count }}
            </strong>

            <span class="text-sm text-anufish-muted">
                Artikel
            </span>
        </div>

        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
            <strong class="block text-xl">
                {{ $user->approved_count }}
            </strong>

            <span class="text-sm text-anufish-muted">
                Disetujui
            </span>
        </div>

    </div>
</x-ui.card>