@extends('layouts.guest')

@section('title', 'Login - Anufish')

@section('page-content')
    <x-auth.card
        title="Login Pengguna"
        description="Gunakan email dan password untuk masuk ke dashboard."
    >
        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf

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

            <x-auth.password-field
                label="Password"
                name="password"
                placeholder="Masukkan password"
                autocomplete="current-password"
                :required="true"
            />

            <x-ui.button type="submit" class="w-full">
                Masuk ke Dashboard
            </x-ui.button>
        </form>

        <p class="mt-5 text-center text-sm text-anufish-muted">
            Belum punya akun?
            <span class="font-bold text-anufish-teal">Daftar</span>
        </p>
    </x-auth.card>
@endsection