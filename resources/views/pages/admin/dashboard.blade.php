@extends('layouts.authenticated')

@section('title', __('Dashboard Admin') . ' - Anufish')

@section('page-content')
    <div class="space-y-6">
        <h1 class="page-title">{{ __('Dashboard Admin') }}</h1>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.artikel.index') }}" class="primary-action">
                {{ __('article.admin_moderation.list_title') }}
            </a>
            <a href="{{ route('admin.users.index') }}" class="secondary-action">
                {{ __('admin_users.list_title') }}
            </a>
        </div>
    </div>
@endsection
