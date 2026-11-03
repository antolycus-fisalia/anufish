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
            'judul.required' => __('article.validation.title_required'),
            'judul.string' => __('article.validation.title_string'),
            'judul.max' => __('article.validation.title_max'),
            'kategori.required' => __('article.validation.category_required'),
            'kategori.string' => __('article.validation.category_string'),
            'kategori.max' => __('article.validation.category_max'),
            'isi.required' => __('article.validation.content_required'),
            'isi.string' => __('article.validation.content_string'),
            'isi.max' => __('article.validation.content_max'),
            'gambar.file' => __('article.validation.image_invalid'),
            'gambar.max' => __('article.validation.image_max'),
            'gambar.extensions' => __('article.validation.image_format'),
            'gambar.mimetypes' => __('article.validation.image_invalid'),
        ];
    }
}
