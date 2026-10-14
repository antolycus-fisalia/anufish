@props([
    'title',
    'description',
])

<article
    {{ $attributes->class('w-full max-w-md rounded-2xl border border-anufish-border bg-white p-6 lg:border-0 lg:p-10') }}>
    <div>
        <div class="mb-4 flex justify-end"><x-language-switch /></div>
        <div class="brand-logo brand-logo-auth mb-8 lg:hidden"><img src="{{ asset('images/anufish-logo.png') }}" alt="Anufish" width="475" height="488" decoding="async"></div>

        <header class="mb-6">
            <h1 class="text-2xl font-semibold leading-8 text-anufish-navy">
                {{ $title }}
            </h1>

            <p class="mt-4 text-sm leading-[22px] text-anufish-muted">
                {{ $description }}
            </p>
        </header>

        {{ $slot }}
        <p class="mt-4 text-xs leading-[18px] text-anufish-muted">{{ __('Akun admin disediakan oleh pengelola.') }}</p>
    </div>
</article>
