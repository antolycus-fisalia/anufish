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
            'operation_id' => ['sometimes', 'required', 'uuid'],
            'fish_image' => [
                'required',
                'file',
                'image',
                'extensions:jpg,jpeg,png',
                'mimes:jpg,jpeg,png',
                'mimetypes:image/jpeg,image/png',
                'max:5120',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'operation_id.required' => __('history.operation_invalid'),
            'operation_id.uuid' => __('history.operation_invalid'),
            'fish_image.required' => __('Foto ikan wajib diunggah.'),
            'fish_image.file' => __('File yang diunggah tidak valid.'),
            'fish_image.image' => __('File harus berupa gambar.'),
            'fish_image.extensions' => __('Format gambar harus JPG, JPEG, atau PNG.'),
            'fish_image.mimes' => __('Format gambar harus JPG, JPEG, atau PNG.'),
            'fish_image.mimetypes' => __('Tipe MIME gambar tidak valid.'),
            'fish_image.max' => __('Ukuran gambar maksimal adalah 5 MB.'),
        ];
    }
}
