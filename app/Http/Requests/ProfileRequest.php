<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
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
                Rule::unique('users', 'email')
                    ->ignore($this->user()->id),
            ],

            'profile_photo' => [
                'nullable',
                'file',
                'max:5120',
                'extensions:jpg,jpeg,png,webp',
                'mimetypes:image/jpeg,image/png,image/webp',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',

            'profile_photo.max' =>
                'Ukuran foto maksimal 5 MB.',

            'profile_photo.extensions' =>
                'Ekstensi foto harus JPG, JPEG, PNG, atau WEBP.',

            'profile_photo.mimetypes' =>
                'File harus berupa gambar JPEG, PNG, atau WEBP.',
        ];
    }
}
