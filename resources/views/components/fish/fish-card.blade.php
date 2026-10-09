@props(['fish'])

<article class="rounded-2xl border border-anufish-border bg-white p-5">
    <h3 class="text-lg font-bold text-anufish-navy">
        {{ $fish['common_name'] ?? $fish['canonical_name'] ?? $fish['scientific_name'] ?? 'Nama tidak tersedia' }}
    </h3>
    <p class="mt-1 text-sm italic text-anufish-muted">
        {{ $fish['scientific_name'] ?? '-' }}
    </p>
    <dl class="mt-4 space-y-2 text-sm">
        @foreach (['class' => 'Kelas', 'order' => 'Ordo', 'family' => 'Famili', 'genus' => 'Genus'] as $key => $label)
            <div class="flex justify-between gap-3">
                <dt class="text-anufish-muted">{{ $label }}</dt>
                <dd class="text-right font-medium text-anufish-navy">{{ $fish['taxonomy'][$key] ?? '-' }}</dd>
            </div>
        @endforeach
    </dl>
</article>
