@extends('layouts.app')

@section('title', __('scan.reference.opening_title').' - Anufish')

@section('content')
    <main class="grid min-h-dvh place-items-center bg-anufish-pale p-6">
        <section class="w-full max-w-md rounded-2xl border border-anufish-border bg-white p-8 text-center" role="status" aria-live="polite">
            <span class="mx-auto mb-6 block size-8 animate-spin rounded-full border-4 border-anufish-border border-t-anufish-teal motion-reduce:animate-none" aria-hidden="true"></span>
            <h1 class="text-2xl font-semibold text-anufish-navy">{{ __('scan.reference.opening_title') }}</h1>
            <p class="mt-4 text-sm text-anufish-muted">{{ __('scan.reference.opening_body') }}</p>
        </section>
    </main>
@endsection
