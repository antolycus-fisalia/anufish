@extends('layouts.authenticated')

@section('title', __('Riwayat Scan - Anufish'))

@section('page-content')
    <div class="scan-history-page">
        <header class="scan-history-header">
            <div>
                <h1 class="page-title">
                    {{ __('Riwayat Scan') }}
                </h1>

                <p>
                    {{ __('Lihat kembali hasil identifikasi ikan yang pernah kamu lakukan.') }}
                </p>
            </div>

            <a
                href="{{ route('scan.index') }}"
                class="primary-action"
            >
                {{ __('Scan Ikan Baru') }}
            </a>
        </header>

        <section class="scan-history-panel">
            @if ($histories->isNotEmpty())
                <div class="scan-history-list">
                    @foreach ($histories as $history)
                        <article class="scan-history-card">
                            <x-scan.history-photo :history="$history" />
                            <div>
                                <p>{{ $history->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</p>
                                <h2><bdi>{{ $history->scientific_name ?? $history->candidates[0]['scientific_name'] }}</bdi></h2>
                                <p>{{ $history->scientific_name ? __('history.selected') : __('history.unselected') }}</p>
                                <a href="{{ route('scan.history.show', $history) }}" class="scan-history-secondary-action">{{ __('history.view') }}</a>
                            </div>
                        </article>
                    @endforeach
                </div>
                {{ $histories->links() }}
            @else
            <div class="scan-history-empty">
                <x-navigation.icon name="history" />

                <div>
                    <h2>{{ __('Belum ada riwayat scan') }}</h2>

                    <p>
                        {{ __('Riwayat hasil identifikasi ikan akan muncul di sini setelah kamu melakukan scan.') }}
                    </p>
                </div>

                <a
                    href="{{ route('scan.index') }}"
                    class="scan-history-secondary-action"
                >
                    {{ __('Mulai Scan') }}
                </a>
            </div>
            @endif
        </section>
    </div>
@endsection
