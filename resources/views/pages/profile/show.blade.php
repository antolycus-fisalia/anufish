@extends('layouts.authenticated')

@section('title', 'Profil - Anufish')

@section('page-content')
    <div
        class="mx-auto max-w-3xl"
        x-data="profileForm"
    >
        {{-- Header halaman --}}
        <div class="mb-6">
            <h1 class="text-3xl font-bold tracking-tight text-anufish-navy">
                Profil Saya
            </h1>

            <p class="mt-2 text-sm leading-6 text-anufish-muted">
                Lihat dan perbarui informasi profil Anda.
            </p>
        </div>

        {{-- Success feedback --}}
        @if (session('success'))
            <div
                class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700"
                role="status"
            >
                {{ session('success') }}
            </div>
        @endif

        {{-- Card profil --}}
        <div class="rounded-2xl border border-anufish-border bg-white p-6 shadow-sm sm:p-8">

            {{-- Ringkasan profil --}}
            <div class="mb-8 flex flex-col items-center gap-4 sm:flex-row">

                {{-- Foto profil --}}
                <div
                    class="size-24 shrink-0 overflow-hidden rounded-full border border-anufish-border bg-anufish-pale"
                >
                    {{-- Preview foto baru --}}
                    <template x-if="previewUrl">
                        <img
                            :src="previewUrl"
                            alt="Preview foto profil"
                            class="size-full object-cover"
                        >
                    </template>

                    {{-- Foto profil tersimpan --}}
                    <template x-if="!previewUrl">
                        <div class="size-full">
                            @if ($user->profile_photo_path)
                                <img
                                    src="{{ asset('storage/' . $user->profile_photo_path) }}"
                                    alt="Foto profil {{ $user->name }}"
                                    class="size-full object-cover"
                                >
                            @else
                                <div
                                    class="flex size-full items-center justify-center text-2xl font-bold text-anufish-navy"
                                    aria-label="Inisial pengguna"
                                >
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                    </template>
                </div>

                {{-- Informasi profil --}}
                <div class="text-center sm:text-left">
                    <h2 class="text-xl font-bold text-anufish-navy">
                        {{ $user->name }}
                    </h2>

                    <p class="mt-1 text-sm text-anufish-muted">
                        {{ $user->email }}
                    </p>

                    <span
                        class="mt-2 inline-flex rounded-full bg-anufish-pale px-3 py-1 text-xs font-semibold text-anufish-teal"
                    >
                        {{ ucfirst($user->status) }}
                    </span>
                </div>
            </div>

            {{-- Form edit profil --}}
            <form
                class="space-y-5"
                enctype="multipart/form-data"
                x-on:submit.prevent="startSubmitting()"
            >
                {{-- Nama --}}
                <x-ui.form-field
                    label="Nama"
                    name="name"
                    :value="old('name', $user->name)"
                    autocomplete="name"
                    :required="true"
                />

                {{-- Email --}}
                <x-ui.form-field
                    label="Email"
                    name="email"
                    type="email"
                    :value="old('email', $user->email)"
                    autocomplete="email"
                    :required="true"
                />

                {{-- Foto profil --}}
                <div>
                    <label
                        for="profile_photo"
                        class="mb-2 block text-sm font-semibold text-anufish-navy"
                    >
                        Foto Profil
                    </label>

                    <input
                        id="profile_photo"
                        name="profile_photo"
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        x-on:change="previewPhoto($event)"
                        class="block w-full rounded-lg border border-anufish-border bg-white px-4 py-3 text-sm text-anufish-navy shadow-sm outline-none transition
                               file:mr-4 file:rounded-md file:border-0 file:bg-anufish-pale file:px-4 file:py-2
                               file:text-sm file:font-semibold file:text-anufish-teal
                               hover:border-anufish-cyan
                               focus:border-anufish-teal focus:ring-4 focus:ring-anufish-pale"
                    >

                    <p class="mt-2 text-sm leading-6 text-anufish-muted">
                        JPG, PNG, atau WebP. Maksimal 5 MB.
                    </p>

                    <x-ui.validation-error
                        name="profile_photo"
                        id="profile_photo"
                    />
                </div>

                {{-- Tombol simpan --}}
                <div class="pt-2">
                    <x-ui.button
                        type="submit"
                        class="w-full sm:w-auto"
                        x-bind:disabled="submitting"
                    >
                        <span x-show="!submitting">
                            Simpan Perubahan
                        </span>

                        <span
                            x-cloak
                            x-show="submitting"
                        >
                            Menyimpan...
                        </span>
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>
@endsection