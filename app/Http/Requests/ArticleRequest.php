<?php

namespace App\Http\Requests;

use App\Models\Artikel;
use App\Rules\ArticleImage;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $artikel = $this->route('artikel');

        return $artikel instanceof Artikel
            ? Gate::allows('update', $artikel)
            : Gate::allows('create', Artikel::class);
    }

    protected function failedAuthorization(): void
    {
        throw new AuthorizationException(__('article.messages.forbidden'));
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
                'bail',
                'nullable',
                'file',
                'max:5120',
                'image',
                'mimes:jpg,jpeg,png',
                'extensions:jpg,jpeg,png',
                'mimetypes:image/jpeg,image/png',
                new ArticleImage,
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
            'gambar.image' => __('article.validation.image_invalid'),
            'gambar.mimes' => __('article.validation.image_invalid'),
            'gambar.max' => __('article.validation.image_max'),
            'gambar.extensions' => __('article.validation.image_format'),
            'gambar.mimetypes' => __('article.validation.image_invalid'),
        ];
    }
}
