<x-layouts.guest title="Login Pengguna">
    <x-ui.card class="w-full shadow-xl shadow-slate-200/70">
        <header class="text-center">
            <x-brand.mark class="justify-center" />

            <h1 class="mt-6 text-2xl font-bold tracking-tight text-anufish-text sm:text-3xl">
                Login Pengguna
            </h1>

            <p class="mt-2 text-sm leading-6 text-anufish-muted sm:text-base">
                Masuk untuk melanjutkan ke dashboard Anufish.
            </p>
        </header>

        <form
            class="mt-8 space-y-5"
            method="POST"
            action="{{ route('login.store') }}"
        >
            @csrf

            <x-ui.input
                label="Email"
                name="email"
                type="email"
                :value="old('email')"
                autocomplete="email"
                required
                autofocus
            />

            <x-ui.input
                label="Kata Sandi"
                name="password"
                type="password"
                autocomplete="current-password"
                required
            />

            <x-ui.button
                class="w-full"
                type="submit"
            >
                Masuk ke Dashboard
            </x-ui.button>
        </form>

        <div class="mt-6 flex gap-3 rounded-xl border border-cyan-200 bg-cyan-50 p-4 text-sm leading-6 text-cyan-900">
            <svg
                class="mt-1 size-4 shrink-0"
                viewBox="0 0 20 20"
                fill="currentColor"
                aria-hidden="true"
            >
                <path
                    fill-rule="evenodd"
                    d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm1-11a1 1 0 1 0-2 0 1 1 0 0 0 2 0Zm-1 2a1 1 0 0 0-1 1v4a1 1 0 1 0 2 0v-4a1 1 0 0 0-1-1Z"
                    clip-rule="evenodd"
                />
            </svg>

            <p>
                Gunakan akun yang telah terdaftar untuk mengakses dashboard.
            </p>
        </div>

        <p class="mt-6 text-center text-sm text-anufish-muted">
            Belum punya akun?
            <a
                class="font-semibold text-anufish-blue underline-offset-4 transition-colors hover:text-cyan-700 hover:underline focus-visible:rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-anufish-cyan focus-visible:ring-offset-2"
                href="{{ route('register') }}"
            >
                Daftar sekarang
            </a>
        </p>
    </x-ui.card>
</x-layouts.guest>
