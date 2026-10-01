@extends('layouts.app')

@section('content')
    <main class="flex min-h-dvh items-center justify-center overflow-x-hidden bg-anufish-pale px-4 py-4 sm:px-6 sm:py-8">
        <div class="w-full max-w-md">
            @yield('page-content')
        </div>
    </main>
@endsection
