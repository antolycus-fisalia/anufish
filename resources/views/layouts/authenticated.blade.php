@extends('layouts.app')

@section('content')

    <div class="min-h-screen">

        <header class="border-b bg-white">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">

                <a href="/" class="text-xl font-semibold">
                    Anufish
                </a>

                <nav>
                    {{-- Navigation --}}
                </nav>

            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            @yield('page-content')
        </main>

    </div>

@endsection
