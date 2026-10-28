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
                    ->ignore($this->user()),
            ],

            'profile_photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
                'dimensions:min_width=32,min_height=32',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('Nama wajib diisi.'),
            'name.string' => __('Nama harus berupa teks.'),
            'name.max' => __('Nama maksimal 255 karakter.'),

            'email.required' => __('Email wajib diisi.'),
            'email.email' => __('Format email tidak valid.'),
            'email.max' => __('Email maksimal 255 karakter.'),
            'email.unique' => __('Email sudah digunakan.'),

            'profile_photo.image' => __('File harus berupa gambar.'),
            'profile_photo.mimes' => __('Foto harus berformat JPG, JPEG, PNG, atau WebP.'),
            'profile_photo.max' => __('Ukuran foto maksimal 5 MB.'),
            'profile_photo.dimensions' => __('Foto minimal berukuran 32 × 32 piksel.'),
        ];
    }
}
