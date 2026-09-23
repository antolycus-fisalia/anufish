<x-layouts.guest title="Pendaftaran Pengguna">
    <x-ui.card class="w-full shadow-xl shadow-slate-200/70">
        <header class="text-center">
            <x-brand.mark class="justify-center" />

            <h1 class="mt-6 text-2xl font-bold tracking-tight text-anufish-text sm:text-3xl">
                Pendaftaran Pengguna
            </h1>

            <p class="mt-2 text-sm leading-6 text-anufish-muted sm:text-base">
                Pendaftaran akun Anufish akan segera tersedia.
            </p>
        </header>

        <div class="mt-8 rounded-xl border border-cyan-200 bg-cyan-50 p-5 text-center text-sm leading-6 text-cyan-900">
            Untuk saat ini, hubungi pengelola Anufish jika Anda memerlukan akun baru.
        </div>

        <a
            class="mt-6 flex w-full items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-3 font-semibold text-anufish-blue transition-colors hover:border-anufish-cyan hover:bg-cyan-50 active:bg-cyan-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-anufish-cyan focus-visible:ring-offset-2"
            href="{{ route('login') }}"
        >
            Kembali ke Login
        </a>
    </x-ui.card>
</x-layouts.guest>
