<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScanUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'fish_image' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png',
                'max:5120', // 5 MB in kilobytes
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'fish_image.required' => __('Foto ikan wajib diunggah.'),
            'fish_image.file'     => __('Unggahan harus berupa file.'),
            'fish_image.image'    => __('File harus berupa gambar.'),
            'fish_image.mimes'    => __('Foto harus berformat JPG, JPEG, atau PNG.'),
            'fish_image.max'      => __('Ukuran foto maksimal 5 MB.'),
        ];
    }
}

