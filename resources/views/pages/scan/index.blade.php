@extends('layouts.authenticated')

@section('title', __('Scan Ikan - Anufish'))

@section('page-content')
    <div class="scan-page">
        <header class="scan-header">
            <h1 class="page-title">
                {{ __('Scan Ikan') }}
            </h1>

            <p>
                {{ __('Ambil atau unggah foto ikan untuk memulai proses identifikasi.') }}
            </p>
        </header>

        <x-scan.upload-panel />
    </div>
@endsection
