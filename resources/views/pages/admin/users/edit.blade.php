@extends('layouts.authenticated')

@section('title', __('admin_users.edit_title') . ' - Anufish')

@section('page-content')
    <div class="max-w-2xl">
        <header class="mb-6">
            <h1 class="page-title">{{ __('admin_users.edit_title') }}</h1>
            <p class="mt-2 text-sm leading-6 text-anufish-muted">{{ __('admin_users.edit_description') }}</p>
        </header>

        @if (session('success'))
            <p role="status" class="mb-6 rounded-lg bg-anufish-pale p-4 text-sm text-anufish-navy">{{ session('success') }}</p>
        @endif

        <form action="{{ route('admin.users.update', $user) }}" method="POST" class="profile-panel space-y-5" data-submit-form="submitting">
            @csrf
            @method('PUT')
            @error('update')
                <p role="alert" class="text-sm text-rose-600">{{ $message }}</p>
            @enderror
            <x-ui.form-field :label="__('admin_users.name')" name="name" :value="old('name', $user->name)" autocomplete="off" :required="true" maxlength="255" />
            <x-ui.form-field label="Email" name="email" type="email" :value="old('email', $user->email)" autocomplete="off" :required="true" maxlength="255" />
            <p class="text-sm leading-6 text-anufish-muted">{{ __('admin_users.edit_limit') }}</p>
            <x-ui.button type="submit" class="w-full" data-submit-button>
                <span data-form-state="idle">{{ __('admin_users.save') }}</span>
                <span hidden data-form-state="submitting">{{ __('admin_users.saving') }}</span>
            </x-ui.button>
        </form>
    </div>
@endsection
