<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Registrasi - Anufish</title>

    @vite('resources/css/app.css')
</head>

<body class="min-h-screen bg-anufish-background text-anufish-text">

    <main class="flex min-h-screen items-center justify-center px-4 py-8">

        <x-ui.card class="w-full max-w-md shadow-sm">

            {{-- Header --}}
            <div class="mb-6 text-center">

                <div
                    class="mx-auto flex h-16 w-16 items-center justify-center
                           rounded-2xl bg-gradient-to-br
                           from-anufish-blue to-anufish-cyan
                           text-3xl text-white"
                >
                    🐟
                </div>

                <h1 class="mt-4 text-2xl font-bold text-anufish-text">
                    Buat Akun Anufish
                </h1>

                <p class="mt-2 text-sm leading-6 text-anufish-muted">
                    Daftar untuk mulai menggunakan fitur Anufish.
                </p>

            </div>


            {{-- Alert global --}}
            @if ($errors->any())
                <x-ui.alert
                    type="error"
                    class="mb-5"
                >
                    Periksa kembali data registrasi Anda.
                </x-ui.alert>
            @endif


            {{-- Form Registrasi --}}
            <form
                method="POST"
                action="{{ route('register.store') }}"
                class="space-y-5"
            >
                @csrf


                {{-- Nama --}}
                <x-ui.input
                    label="Nama Lengkap"
                    name="nama"
                    type="text"
                    value="{{ old('nama') }}"
                    autocomplete="name"
                    placeholder="Masukkan nama lengkap"
                    required
                />


                {{-- Email --}}
                <x-ui.input
                    label="Email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    autocomplete="email"
                    placeholder="nama@email.com"
                    required
                />


                {{-- Password --}}
                <x-ui.input
                    label="Password"
                    name="password"
                    type="password"
                    autocomplete="new-password"
                    placeholder="Minimal 8 karakter"
                    required
                />

                <p class="-mt-3 text-xs text-anufish-muted">
                    Gunakan minimal 8 karakter.
                </p>


                {{-- Konfirmasi Password --}}
                <x-ui.input
                    label="Konfirmasi Password"
                    name="password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    placeholder="Ulangi password"
                    required
                />


                {{-- Tombol --}}
                <x-ui.button
                    type="submit"
                    variant="primary"
                    class="w-full"
                >
                    Daftar
                </x-ui.button>

            </form>


            {{-- Navigasi login --}}
            <div class="mt-6 border-t border-slate-100 pt-5 text-center">

                <p class="text-sm text-anufish-muted">

                    Sudah punya akun?

                    <a
                        href="{{ route('login') }}"
                        class="font-semibold text-anufish-blue hover:text-anufish-cyan"
                    >
                        Masuk
                    </a>

                </p>

            </div>

        </x-ui.card>

    </main>

</body>
</html>