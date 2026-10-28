@extends('layouts.authenticated')

@section('title', __('Profil - Anufish'))

@section('page-content')
    <div data-profile-page>
        <header class="mb-6">
            <h1 class="page-title">{{ __('Profil Saya') }}</h1>
            <p class="mt-2 text-sm leading-[22px] text-anufish-muted">{{ __('Informasi pribadi dan pengaturan akun.') }}</p>
        </header>

        @if (session('success'))
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700"
                role="status">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="profile-grid"
            data-profile-form data-submit-form="submitting">
            @csrf
            @method('PUT')

            <section class="profile-panel space-y-4" aria-label="{{ __('Ringkasan profil') }}">
                <div class="size-20 overflow-hidden rounded-2xl bg-anufish-teal">
                    <img hidden data-profile-preview alt="{{ __('Preview foto profil') }}" class="size-full object-cover">
                    <div data-profile-original class="size-full">
                        <div class="relative size-full">
                            <span class="flex size-full items-center justify-center text-2xl font-semibold text-white"
                                aria-label="{{ __('Inisial pengguna') }}">{{ mb_strtoupper(mb_substr($user->name, 0, 2)) }}</span>
                            @if ($photoUrl)
                                <img data-profile-photo src="{{ $photoUrl }}"
                                    alt="{{ __('Foto profil') }} {{ $user->name }}" class="absolute inset-0 size-full object-cover">
                            @endif
                        </div>
                    </div>
                </div>
                <h2 class="break-words text-2xl font-semibold leading-8 text-anufish-navy">{{ $user->name }}</h2>
                <p class="break-words text-sm text-anufish-muted">{{ $user->email }}</p>
                <span
                    class="inline-flex rounded-lg bg-anufish-pale px-2 py-1 text-xs font-medium text-anufish-teal">{{ __(ucfirst($user->status)) }}</span>

                <div>
                    <label for="profile_photo"
                        class="mb-2 block text-sm font-semibold text-anufish-navy">{{ __('Ubah foto profil') }}</label>
                    <input id="profile_photo" name="profile_photo" type="file" accept="image/jpeg,image/png,image/webp"
                         aria-invalid="{{ $errors->has('profile_photo') ? 'true' : 'false' }}"
                        aria-describedby="profile-photo-help{{ $errors->has('profile_photo') ? ' profile_photo-error' : '' }}"
                        class="block min-h-11 w-full min-w-0 rounded-lg border border-anufish-border bg-white text-sm text-anufish-muted file:mr-2 file:min-h-11 file:border-0 file:bg-anufish-pale file:px-3 file:font-semibold file:text-anufish-teal hover:border-anufish-cyan focus-visible:outline-2 focus-visible:outline-anufish-teal">
                    <p id="profile-photo-help" class="mt-2 text-xs leading-[18px] text-anufish-muted">
                        {{ __('JPG, PNG, atau WebP. Maksimal 5 MB. Minimal 32 × 32 piksel.') }}</p>
                    <x-ui.validation-error name="profile_photo" id="profile_photo" />
                </div>
            </section>

            <section class="profile-panel space-y-5" aria-labelledby="profile-information">
                <h2 id="profile-information" class="text-2xl font-semibold leading-8 text-anufish-navy">
                    {{ __('Informasi pribadi') }}</h2>
                <x-ui.form-field :label="__('Nama lengkap')" name="name" :value="old('name', $user->name)" autocomplete="name" :required="true" />
                <x-ui.form-field label="Email" name="email" type="email" :value="old('email', $user->email)" autocomplete="email"
                    :required="true" />
                <p class="text-sm leading-[22px] text-anufish-muted">{{ __('Email digunakan untuk masuk ke akunmu.') }}</p>
                <x-ui.button type="submit" class="w-full" data-submit-button>
                    <span data-form-state="idle">{{ __('Simpan Perubahan') }}</span>
                    <span hidden data-form-state="submitting">{{ __('Menyimpan...') }}</span>
                </x-ui.button>
            </section>
        </form>
    </div>
@endsection
