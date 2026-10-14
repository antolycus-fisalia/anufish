@extends('layouts.guest')

@section('title', __('Registrasi - Anufish'))

@section('page-content')
    <x-auth.card :title="__('Mulai perjalananmu')" :description="__('Daftar untuk mulai menggunakan Anufish.')">
        <form action="{{ route('register.store') }}" method="POST" class="space-y-4" data-submit-form="submitting"
            >
            @csrf

            <x-ui.form-field :label="__('Nama lengkap')" name="name" type="text" :value="old('name')" :placeholder="__('Nama lengkap')"
                autocomplete="name" :required="true" :autofocus="true" />

            <x-ui.form-field label="Email" name="email" type="email" :value="old('email')" :placeholder="__('nama@contoh.com')"
                autocomplete="email" :required="true" />

            <x-auth.password-field label="Password" name="password" :placeholder="__('Minimal 8 karakter')" autocomplete="new-password"
                :required="true" />

            <p class="text-xs leading-[18px] text-anufish-muted">{{ __('Minimal 8 karakter.') }}</p>

            <x-auth.password-field :label="__('Konfirmasi Password')" name="password_confirmation" :placeholder="__('Ulangi password')"
                autocomplete="new-password" :required="true" />

            <x-ui.button type="submit" class="w-full" data-submit-button>
                <span data-form-state="idle">
                    {{ __('Buat akun') }}
                </span>

                <span hidden data-form-state="submitting">
                    {{ __('Memproses...') }}
                </span>
            </x-ui.button>
        </form>

        <p class="mt-4 flex flex-wrap items-center gap-2 text-sm text-anufish-muted">
            {{ __('Sudah punya akun?') }}
            <a href="{{ route('login') }}"
                class="inline-flex min-h-11 items-center rounded font-semibold text-anufish-teal hover:underline focus-visible:outline-2 focus-visible:outline-anufish-teal">
                {{ __('Masuk') }}
            </a>
        </p>
    </x-auth.card>
@endsection
