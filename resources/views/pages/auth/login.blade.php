@extends('layouts.guest')

@section('title', 'Login - Anufish')

@section('page-content')
    <x-auth.card
        title="Login Pengguna"
        description="Gunakan email dan password untuk masuk ke dashboard."
    >

        @if (session('success'))
            <div
                class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700"
                role="status"
            >
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('login.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <x-ui.form-field
                    label="Email"
                    name="email"
                    type="email"
                    :value="old('email')"
                    placeholder="nama@contoh.com"
                    autocomplete="email"
                    :required="true"
                    :autofocus="true"
                />

                @error('email')
                    <p class="mt-1 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div>
                <x-auth.password-field
                    label="Password"
                    name="password"
                    placeholder="Masukkan password"
                    autocomplete="current-password"
                    :required="true"
                />

                @error('password')
                    <p class="mt-1 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <x-ui.button type="submit" class="w-full">
                Masuk ke Dashboard
            </x-ui.button>
        </form>

        <p class="mt-5 text-center text-sm text-anufish-muted">
            Belum punya akun?

            <a
                href="{{ route('register') }}"
                class="font-bold text-anufish-teal hover:underline"
            >
                Daftar
            </a>
        </p>

    </x-auth.card>
@endsection
