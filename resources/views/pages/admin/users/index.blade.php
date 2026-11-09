@extends('layouts.authenticated')

@section('title', __('admin_users.list_title') . ' - Anufish')

@section('page-content')
<div class="space-y-6">
    <header>
        <h1 class="text-3xl font-bold text-anufish-navy">
            {{ __('admin_users.list_title') }}
        </h1>
        <p class="mt-2 text-anufish-muted">
            {{ __('admin_users.list_description') }}
        </p>
    </header>

    <section class="rounded-2xl border border-anufish-border bg-white p-4 sm:p-6">
        <form method="GET" action="{{ route('admin.users.index') }}"
              class="flex flex-col gap-3 sm:flex-row">
            <label for="search" class="sr-only">
                {{ __('admin_users.search') }}
            </label>
            <input
                id="search"
                type="search"
                name="search"
                value="{{ $search }}"
                maxlength="100"
                placeholder="{{ __('admin_users.search') }}"
                class="min-h-11 w-full rounded-lg border border-anufish-border px-4 focus:border-anufish-teal focus:outline-none focus:ring-2 focus:ring-anufish-teal/20"
            >
            <button type="submit"
                    class="min-h-11 rounded-lg bg-anufish-teal px-5 font-semibold text-white hover:bg-anufish-navy">
                {{ __('admin_users.search_button') }}
            </button>
        </form>
    </section>

    <section class="overflow-hidden rounded-2xl border border-anufish-border bg-white">
        @forelse ($users as $user)
            <article class="flex flex-col gap-3 border-b border-anufish-border/50 p-4 last:border-b-0 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <h2 class="font-semibold text-anufish-navy">
                        {{ $user->name }}
                    </h2>
                    <p class="break-all text-sm text-anufish-muted">
                        {{ $user->email }}
                    </p>
                    <p class="mt-1 text-sm text-anufish-muted">
                        {{ $user->role }} · {{ $user->status }}
                    </p>
                </div>
                <a href="{{ route('admin.users.show', $user->id) }}"
                class="inline-flex min-h-11 items-center justify-center rounded-lg border border-anufish-border px-4 font-semibold text-anufish-teal hover:bg-anufish-pale focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-anufish-teal">
                    {{ __('admin_users.view_detail') }}
                </a>
            </article>
        @empty
            <div class="p-8 text-center">
                <p class="font-semibold text-anufish-navy">
                    {{ __('admin_users.empty') }}
                </p>
            </div>
        @endforelse
    </section>

    @if ($users->hasPages())
        <nav aria-label="{{ __('admin_users.pagination') }}">
            {{ $users->links() }}
        </nav>
    @endif
</div>
@endsection
