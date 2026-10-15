@php($user = auth()->user())
<div class="flex h-full min-h-0 flex-col bg-white p-4">
    <div class="flex h-24 shrink-0 items-center justify-between">
        <a href="{{ route('homepage') }}" aria-label="{{ __('Anufish - Beranda') }}"
            class="brand-logo rounded-lg focus-visible:outline-2 focus-visible:outline-anufish-teal"><img
                src="{{ asset('images/anufish-logo.png') }}" alt="Anufish" width="475" height="488"></a>
        {{ $slot }}
    </div>
    <nav aria-label="{{ __('Navigasi utama') }}" class="min-h-0 flex-1 overflow-y-auto py-4">
        <h2 class="mb-2 px-3 text-xs font-medium text-anufish-muted">{{ __('Eksplorasi') }}</h2>
        <ul class="space-y-1">
            <x-navigation.item icon="home" :href="route('homepage')"
                :active="request()->routeIs('homepage')">{{ __('Beranda') }}</x-navigation.item>
            <x-navigation.item icon="fish" :href="route('fish.index')"
                :active="request()->routeIs('fish.*')">{{ __('Jelajahi Ikan') }}</x-navigation.item>
            <x-navigation.item icon="article">{{ __('Artikel') }}</x-navigation.item>
        </ul>
        <h2 class="mb-2 mt-6 px-3 text-xs font-medium text-anufish-muted">{{ __('Ruang saya') }}</h2>
        <ul class="space-y-1">
            <x-navigation.item icon="scan">{{ __('Scan Ikan') }}</x-navigation.item>
            <x-navigation.item icon="history">{{ __('Riwayat Scan') }}</x-navigation.item>
            <x-navigation.item icon="edit">{{ __('Artikel Saya') }}</x-navigation.item>
        </ul>
        @if ($user->isAdmin())
            <h2 class="mb-2 mt-6 px-3 text-xs font-semibold text-anufish-muted">Admin</h2>
            <ul><x-navigation.item icon="grid" :href="route('admin.dashboard')">{{ __('Dashboard Admin') }}</x-navigation.item>
            </ul>
        @endif
    </nav>
    <div class="shrink-0 space-y-2 pt-3">
        <a href="{{ route('profile.show') }}" @if (request()->routeIs('profile.*')) aria-current="page" @endif
            class="flex min-h-11 items-center gap-3 rounded-lg border border-anufish-border p-3 hover:bg-anufish-pale focus-visible:outline-2 focus-visible:outline-anufish-teal">
            <span
                class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-anufish-teal text-sm font-semibold text-white"
                aria-hidden="true">{{ mb_strtoupper(mb_substr($user->name, 0, 2)) }}</span>
            <span class="min-w-0"><span
                    class="block truncate text-sm font-semibold text-anufish-navy">{{ $user->name }}</span><span
                    class="block text-xs text-anufish-muted">{{ $user->role === 'admin' ? 'Administrator' : __('Pengguna aktif') }}</span></span>
        </a>
        <form method="POST" action="{{ route('logout') }}">@csrf
            <button type="submit"
                class="flex min-h-11 w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold text-anufish-muted hover:bg-anufish-pale hover:text-anufish-navy focus-visible:outline-2 focus-visible:outline-anufish-teal"><x-navigation.icon
                    name="logout" /> {{ __('Keluar') }}</button>
        </form>
    </div>
</div>
