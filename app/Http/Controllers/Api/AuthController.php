<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'user',
            'status' => 'aktif',
        ]);

        return response()->json(['data' => $user], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();
        $valid = Auth::guard('web')->validate($credentials);
        $user = $valid ? User::where('email', $credentials['email'])->first() : null;

        if (! $user) {
            throw ValidationException::withMessages(['email' => __('Email atau password salah.')]);
        }
        abort_unless($user->isActive(), 403, __('Akun tidak aktif atau diblokir.'));

        $expiresAt = now()->addDays(30);
        $token = $user->createToken('anufish-api', ['*'], $expiresAt);

        return response()->json([
            'data' => $user,
            'token_type' => 'Bearer',
            'access_token' => $token->plainTextToken,
            'expires_at' => $expiresAt->toISOString(),
        ])->header('Cache-Control', 'no-store');
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['data' => $request->user()]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();
        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        return response()->json(['message' => __('Keluar')]);
    }
}
