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
        </section>
    </div>
@endsection
