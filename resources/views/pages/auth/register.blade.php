@extends('layouts.guest')

@section('title', 'Daftar - Anufish')

@section('page-content')
    <x-auth.card
        title="Registrasi Pengguna"
        description="Buat akun baru untuk menggunakan Anufish."
    >
        <form action="{{ route('register.store') }}" method="POST" class="space-y-4">
            @csrf

            <x-ui.form-field
                label="Nama"
                name="name"
                type="text"
                :value="old('name')"
                placeholder="Nama lengkap"
                autocomplete="name"
                :required="true"
                :autofocus="true"
            />

            <x-ui.form-field
                label="Email"
                name="email"
                type="email"
                :value="old('email')"
                placeholder="nama@contoh.com"
                autocomplete="email"
                :required="true"
            />

            <x-auth.password-field
                label="Password"
                name="password"
                placeholder="Minimal 8 karakter"
                autocomplete="new-password"
                :required="true"
            />

            <x-auth.password-field
                label="Konfirmasi Password"
                name="password_confirmation"
                placeholder="Ulangi password"
                autocomplete="new-password"
                :required="true"
            />

            <x-ui.button type="submit" class="w-full">
                Daftar
            </x-ui.button>
        </form>

        <p class="mt-5 text-center text-sm text-anufish-muted">
            Sudah punya akun?
            <a
                href="{{ route('login') }}"
                class="font-bold text-anufish-teal"
            >
                Login
            </a>
        </p>
    </x-auth.card>
@endsection
