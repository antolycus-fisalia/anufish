@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
    <div class="min-h-screen flex items-center justify-center">
        <div>
            <h1 class="text-4xl font-bold">
                Selamat datang di Anufish
            </h1>

            <p class="mt-2 text-gray-600">
                Website identifikasi dan informasi ikan.
            </p>
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition duration-200 shadow">
                    Logout
                </button>
            </form>
        </div>
    </div>
@endsection
