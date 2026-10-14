@extends('layouts.guest')

@section('title', __('Login - Anufish'))

@section('page-content')
    <x-auth.card :title="__('Selamat datang kembali')" :description="__('Masuk untuk melanjutkan pengamatan dan pengetahuanmu.')">

        @if (session('success'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700"
                role="status">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('login.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <x-ui.form-field label="Email" name="email" type="email" :value="old('email')" :placeholder="__('nama@contoh.com')"
                    autocomplete="email" :required="true" :autofocus="true" />
            </div>

            <div>
                <x-auth.password-field label="Password" name="password" :placeholder="__('Masukkan password')" autocomplete="current-password"
                    :required="true" />
            </div>

            <x-ui.button type="submit" class="w-full">
                {{ __('Masuk') }}
            </x-ui.button>
        </form>

        <p class="mt-4 flex flex-wrap items-center gap-2 text-sm text-anufish-muted">
            {{ __('Belum punya akun?') }}

            <a href="{{ route('register') }}"
                class="inline-flex min-h-11 items-center rounded font-semibold text-anufish-teal hover:underline focus-visible:outline-2 focus-visible:outline-anufish-teal">
                {{ __('Daftar') }}
            </a>
        </p>

    </x-auth.card>
@endsection
