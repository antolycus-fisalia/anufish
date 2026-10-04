<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public function create()
    {
        return view('pages.auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'user',
            'status' => 'aktif',
            'profile_photo_path' => null,
        ]);

        return redirect()
            ->route('login')
            ->with('success', 'Registrasi berhasil. Silakan login.');
    }
}
