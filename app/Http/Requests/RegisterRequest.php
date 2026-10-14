<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'confirmed',
                Password::min(8),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('Nama wajib diisi.'),

            'email.required' => __('Email wajib diisi.'),
            'email.email' => __('Format email tidak valid.'),
            'email.unique' => __('Email sudah terdaftar, gunakan email lain.'),

            'password.required' => __('Password wajib diisi.'),
            'password.confirmed' => __('Konfirmasi password tidak cocok.'),
            'password.min' => __('Password minimal 8 karakter.'),
        ];
    }
}
