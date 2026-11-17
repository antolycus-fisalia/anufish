@extends('layouts.authenticated')

@section('title', __('Dashboard Admin') . ' - Anufish')

@section('page-content')
    <div class="space-y-6">
        <header>
            <h1 class="page-title">{{ __('Dashboard Admin') }}</h1>
            <p class="mt-2 max-w-xl text-anufish-muted">{{ __('admin_dashboard.description') }}</p>
        </header>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
            <section aria-labelledby="admin-moderation-heading" class="min-w-0 overflow-hidden rounded-2xl border border-anufish-border bg-white">
                <div class="space-y-5 border-b border-anufish-border/60 bg-anufish-pale p-4 sm:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-anufish-teal text-white">
                                <x-navigation.icon name="article" />
                            </span>
                            <h2 id="admin-moderation-heading" class="text-xl font-bold text-anufish-navy">
                                {{ __('article.admin_moderation.list_title') }}
                            </h2>
                        </div>

                        <a href="{{ route('admin.artikel.index') }}" class="primary-action w-full sm:w-auto">
                            {{ __('admin_dashboard.open_queue') }}
                            <x-navigation.icon name="chevron-right" />
                        </a>
                    </div>

                    <ul class="grid grid-cols-3 gap-2 sm:gap-3">
                        @foreach (['menunggu', 'disetujui', 'ditolak'] as $status)
                            <li class="min-w-0">
                                <a href="{{ route('admin.artikel.index', ['status' => $status]) }}"
                                   class="flex h-full flex-col-reverse gap-1 rounded-xl border border-anufish-border bg-white p-2 text-anufish-navy hover:border-anufish-teal focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-anufish-teal sm:p-4">
                                    <span class="text-xs font-semibold sm:text-sm">{{ __('article.admin_moderation.statuses.' . $status) }}</span>
                                    <span class="break-words text-2xl font-bold tabular-nums sm:text-[32px] sm:leading-10">{{ $articleCounts->get($status, 0) }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="p-4 sm:p-6">
                    <h3 class="text-base font-bold text-anufish-navy">{{ __('admin_dashboard.recent_pending') }}</h3>

                    @if ($pendingArticles->isNotEmpty())
                        <ul class="mt-2 divide-y divide-anufish-border/60">
                            @foreach ($pendingArticles as $article)
                                <li>
                                    <a href="{{ route('admin.artikel.show', ['artikel' => $article]) }}"
                                       class="flex min-h-11 items-center gap-3 rounded-lg px-2 py-3 text-anufish-navy hover:bg-anufish-pale focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-anufish-teal">
                                        <div class="min-w-0 flex-1">
                                            <p class="break-words text-sm font-semibold">{{ $article->judul }}</p>
                                            <p class="mt-1 break-words text-xs text-anufish-muted">{{ $article->user?->name ?? __('article.admin_moderation.unknown_author') }}</p>
                                        </div>
                                        <x-navigation.icon name="chevron-right" />
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="mt-3 rounded-xl bg-anufish-pale p-4 text-sm text-anufish-muted">
                            {{ __('admin_dashboard.empty_queue') }}
                        </p>
                    @endif
                </div>
            </section>

            <section aria-labelledby="admin-users-heading" class="flex min-w-0 flex-col gap-5 rounded-2xl border border-anufish-border bg-white p-4 sm:p-6">
                <div class="flex items-center gap-3">
                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-anufish-pale text-anufish-teal">
                        <x-navigation.icon name="users" />
                    </span>
                    <h2 id="admin-users-heading" class="text-xl font-bold text-anufish-navy">{{ __('admin_users.list_title') }}</h2>
                </div>

                <dl>
                    <div class="flex flex-col-reverse gap-1 border-b border-anufish-border/60 pb-4">
                        <dt class="text-sm text-anufish-muted">{{ __('admin_dashboard.registered_users') }}</dt>
                        <dd class="text-[32px] leading-10 font-bold text-anufish-navy tabular-nums">{{ $userCounts->sum() }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 border-b border-anufish-border/60 py-3">
                        <dt class="text-sm text-anufish-muted">{{ __('admin_dashboard.active_users') }}</dt>
                        <dd class="font-semibold text-anufish-navy tabular-nums">{{ $userCounts->get('aktif', 0) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 py-3">
                        <dt class="text-sm text-anufish-muted">{{ __('admin_dashboard.blocked_users') }}</dt>
                        <dd class="font-semibold text-anufish-navy tabular-nums">{{ $userCounts->get('diblokir', 0) }}</dd>
                    </div>
                </dl>

                <a href="{{ route('admin.users.index') }}"
                   class="mt-auto inline-flex min-h-11 items-center justify-between gap-2 rounded-lg border border-anufish-border px-4 py-2 text-sm font-semibold text-anufish-navy hover:bg-anufish-pale focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-anufish-teal">
                    {{ __('admin_dashboard.open_users') }}
                    <x-navigation.icon name="chevron-right" />
                </a>
            </section>
        </div>
    </div>
@endsection
