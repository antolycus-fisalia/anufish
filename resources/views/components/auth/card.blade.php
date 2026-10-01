@props([
    'title',
    'description',
])

<article
    {{ $attributes->class('rounded-2xl border border-anufish-border bg-white shadow-xl shadow-anufish-navy/10') }}>
    <div class="px-6 pb-7 pt-6 sm:px-10 sm:pb-8">
        <img src="{{ asset('images/anufish-logo.png') }}" alt="Anufish" width="475" height="488"
            class="mx-auto -my-28 h-auto w-80 max-w-full" decoding="async">

        <header class="mb-6 text-center">
            <h1 class="text-2xl font-bold tracking-tight text-anufish-navy sm:text-3xl">
                {{ $title }}
            </h1>

            <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-anufish-muted">
                {{ $description }}
            </p>
        </header>

        {{ $slot }}
    </div>
</article>
