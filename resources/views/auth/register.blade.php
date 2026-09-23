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

        <section
            class="w-full max-w-md rounded-2xl border border-slate-200
                   bg-white p-6 shadow-sm sm:p-8"
        >

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


            {{-- Form Registrasi --}}
            <form
                method="POST"
                action="{{ route('register.store') }}"
                class="space-y-5"
            >

                @csrf


                {{-- Nama --}}
                <div>

                    <label
                        for="nama"
                        class="mb-1.5 block text-sm font-semibold text-slate-700"
                    >
                        Nama Lengkap
                    </label>

                    <input
                        id="nama"
                        name="nama"
                        type="text"
                        value="{{ old('nama') }}"
                        autocomplete="name"
                        placeholder="Masukkan nama lengkap"
                        required

                        class="w-full rounded-xl border px-3.5 py-2.5
                               text-sm outline-none transition

                               @error('nama')
                                   border-rose-400
                                   focus:border-rose-500
                                   focus:ring-4
                                   focus:ring-rose-100
                               @else
                                   border-slate-300
                                   focus:border-anufish-cyan
                                   focus:ring-4
                                   focus:ring-cyan-100
                               @enderror"
                    >

                    @error('nama')
                        <p class="mt-1.5 text-sm font-medium text-rose-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- Email --}}
                <div>

                    <label
                        for="email"
                        class="mb-1.5 block text-sm font-semibold text-slate-700"
                    >
                        Email
                    </label>

                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        placeholder="nama@email.com"
                        required

                        class="w-full rounded-xl border px-3.5 py-2.5
                               text-sm outline-none transition

                               @error('email')
                                   border-rose-400
                                   focus:border-rose-500
                                   focus:ring-4
                                   focus:ring-rose-100
                               @else
                                   border-slate-300
                                   focus:border-anufish-cyan
                                   focus:ring-4
                                   focus:ring-cyan-100
                               @enderror"
                    >

                    @error('email')
                        <p class="mt-1.5 text-sm font-medium text-rose-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- Password --}}
                <div>

                    <label
                        for="password"
                        class="mb-1.5 block text-sm font-semibold text-slate-700"
                    >
                        Password
                    </label>

                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="new-password"
                        placeholder="Minimal 8 karakter"
                        required

                        class="w-full rounded-xl border px-3.5 py-2.5
                               text-sm outline-none transition

                               @error('password')
                                   border-rose-400
                                   focus:border-rose-500
                                   focus:ring-4
                                   focus:ring-rose-100
                               @else
                                   border-slate-300
                                   focus:border-anufish-cyan
                                   focus:ring-4
                                   focus:ring-cyan-100
                               @enderror"
                    >

                    <p class="mt-1.5 text-xs text-anufish-muted">
                        Gunakan minimal 8 karakter.
                    </p>

                    @error('password')
                        <p class="mt-1.5 text-sm font-medium text-rose-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

{{-- Konfirmasi Password --}}
<div>

    <label
        for="password_confirmation"
        class="mb-1.5 block text-sm font-semibold text-slate-700"
    >
        Konfirmasi Password
    </label>

    <input
        id="password_confirmation"
        name="password_confirmation"
        type="password"
        autocomplete="new-password"
        placeholder="Ulangi password"
        required

        class="w-full rounded-xl border px-3.5 py-2.5
               text-sm outline-none transition

               @error('password')
                   border-rose-400
                   focus:border-rose-500
                   focus:ring-4
                   focus:ring-rose-100
               @else
                   border-slate-300
                   focus:border-anufish-cyan
                   focus:ring-4
                   focus:ring-cyan-100
               @enderror"
    >

            </div>

                {{-- Tombol Daftar --}}
                <button
                    type="submit"
                    class="w-full rounded-xl
                           bg-gradient-to-r
                           from-anufish-blue to-anufish-cyan
                           px-4 py-3
                           text-sm font-semibold text-white
                           shadow-sm transition
                           hover:opacity-95
                           focus:outline-none
                           focus:ring-4
                           focus:ring-cyan-200"
                >
                    Daftar
                </button>

            </form>


            {{-- Navigasi Login --}}
            <div
                class="mt-6 border-t border-slate-100 pt-5 text-center"
            >

                <p class="text-sm text-anufish-muted">

                    Sudah punya akun?

                    <a
                        href="{{ route('login') }}"
                        class="font-semibold text-anufish-blue
                               hover:text-anufish-cyan"
                    >
                        Masuk
                    </a>

                </p>

            </div>

        </section>

    </main>

</body>
</html>