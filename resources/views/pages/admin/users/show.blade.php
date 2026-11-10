@extends('layouts.authenticated')

@section('title', __('admin_users.detail_title') . ' - Anufish')

@section('page-content')
<div class="space-y-6">
    <header>
        <a href="{{ route('admin.users.index') }}"
           class="inline-flex min-h-11 items-center text-sm font-semibold text-anufish-teal hover:underline focus-visible:outline-2 focus-visible:outline-anufish-teal">
            {{ __('admin_users.back_to_list') }}
        </a>

        <h1 class="mt-3 text-3xl font-bold text-anufish-navy">
            {{ __('admin_users.detail_title') }}
        </h1>
    </header>

    <section class="rounded-2xl border border-anufish-border bg-white p-4 sm:p-6">
        <dl class="grid gap-6 sm:grid-cols-2">
            <div>
                <dt class="text-sm text-anufish-muted">
                    {{ __('admin_users.name') }}
                </dt>
                <dd class="mt-1 break-words font-semibold text-anufish-navy">
                    {{ $user->name }}
                </dd>
            </div>

            <div>
                <dt class="text-sm text-anufish-muted">
                    {{ __('admin_users.email') }}
                </dt>
                <dd class="mt-1 break-all text-anufish-navy">
                    {{ $user->email }}
                </dd>
            </div>

            <div>
                <dt class="text-sm text-anufish-muted">
                    {{ __('admin_users.role') }}
                </dt>
                <dd class="mt-1 text-anufish-navy">
                    {{ $user->role }}
                </dd>
            </div>

            <div>
                <dt class="text-sm text-anufish-muted">
                    {{ __('admin_users.status') }}
                </dt>
                <dd class="mt-1 text-anufish-navy">
                    {{ $user->status }}
                </dd>
            </div>

            <div>
                <dt class="text-sm text-anufish-muted">
                    {{ __('admin_users.joined_at') }}
                </dt>
                <dd class="mt-1 text-anufish-navy">
                    {{ $user->created_at?->format('d M Y') }}
                </dd>
            </div>
        </dl>
        <a href="{{ route('admin.users.edit', $user) }}"
           class="primary-action mt-6">{{ __('admin_users.edit_title') }}</a>
    </section>
</div>
@endsection
