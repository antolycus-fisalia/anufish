<?php

namespace App\Http\Requests;

use App\Models\Artikel;
use App\Rules\ArticleImage;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;

class ArticleRequest extends FormRequest
{
    private const MAX_IMAGE_KIB = 5120;

    public static function imageUploadLimit(): int
    {
        return (int) min(self::MAX_IMAGE_KIB * 1024, UploadedFile::getMaxFilesize());
    }

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
                'max:'.self::MAX_IMAGE_KIB,
                'image',
                'mimes:jpg,jpeg,png,webp',
                'extensions:jpg,jpeg,png,webp',
                'mimetypes:image/jpeg,image/png,image/webp',
                new ArticleImage,
            ],
        ];
    }

    public function messages(): array
    {
        $image = $this->file('gambar');
        $uploadTooLarge = $image instanceof UploadedFile
            && in_array($image->getError(), [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true);

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
            'gambar.uploaded' => $uploadTooLarge
                ? __('article.validation.image_server_max')
                : __('article.validation.image_upload_failed'),
            'gambar.image' => __('article.validation.image_invalid'),
            'gambar.mimes' => __('article.validation.image_invalid'),
            'gambar.max' => __('article.validation.image_max'),
            'gambar.extensions' => __('article.validation.image_format'),
            'gambar.mimetypes' => __('article.validation.image_invalid'),
        ];
    }
}
