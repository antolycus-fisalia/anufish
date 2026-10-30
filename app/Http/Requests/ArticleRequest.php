<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'judul' => [
                'required',
                'string',
                'max:255',
            ],

            'kategori' => [
                'required',
                'string',
                'max:100',
            ],

            'isi' => [
                'required',
                'string',
                'max:50000',
            ],

            'gambar' => [
                'nullable',
                'file',
                'max:5120',
                'extensions:jpg,jpeg,png',
                'mimetypes:image/jpeg,image/png',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'judul.required' => 'Judul artikel wajib diisi.',
            'judul.max' => 'Judul artikel maksimal 255 karakter.',

            'kategori.required' => 'Kategori artikel wajib diisi.',

            'isi.required' => 'Isi artikel wajib diisi.',
            'isi.max' => 'Isi artikel terlalu panjang.',

            'gambar.max' => 'Ukuran gambar maksimal 5 MB.',
            'gambar.extensions' => 'Format gambar harus JPG, JPEG, atau PNG.',
            'gambar.mimetypes' => 'Tipe gambar tidak valid.',
        ];
    }
}
