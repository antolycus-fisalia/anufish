<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'user',
            'status' => 'aktif',
            'profile_photo_path' => null,
        ]);

        return redirect()
            ->route('login')
            ->with('success', __('Registrasi berhasil. Silakan login.'));
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        // Verifikasi email + password tanpa membuat session terlebih dahulu.
        if (! Auth::validate($credentials)) {
            return back()
                ->withErrors([
                    'email' => __('Email atau password salah.'),
                ])
                ->onlyInput('email');
        }

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! $user->isActive()) {
            return back()
                ->withErrors([
                    'email' => __('Akun tidak aktif atau diblokir.'),
                ])
                ->onlyInput('email');
        }

        Auth::login($user);

        // Mencegah session fixation.
        $request->session()->regenerate();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('homepage');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
