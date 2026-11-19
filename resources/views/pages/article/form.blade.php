@extends('layouts.authenticated')

@php
    $isEdit = request()->routeIs('artikel-saya.edit');
    $articleId = $isEdit ? request()->route('artikel') : null;
    $imageLimit = \App\Http\Requests\ArticleRequest::imageUploadLimit();
    $imageLimitLabel = $imageLimit % (1024 * 1024) === 0
        ? ($imageLimit / (1024 * 1024)) . ' MB'
        : intdiv($imageLimit, 1024) . ' KB';
@endphp

@section('title', $isEdit ? __('Edit Artikel - Anufish') : __('Buat Artikel - Anufish'))

@section('page-content')
    <div
        class="article-form-page"
        data-article-form
        data-mode="{{ $isEdit ? 'edit' : 'create' }}"
        data-error-load="{{ __('Artikel belum dapat dimuat. Silakan coba lagi.') }}"
        data-error-submit="{{ __('Artikel belum dapat disimpan. Silakan coba lagi.') }}"
        data-error-image-format="{{ __('Format gambar harus JPG, JPEG, PNG, atau WebP.') }}"
        data-error-image-max="{{ __('article.form.image_max', ['limit' => $imageLimitLabel]) }}"
        data-error-image-invalid="{{ __('File gambar tidak dapat dibaca. Pilih gambar lain yang valid.') }}"
        @if ($articleId)
        data-load-url="{{ route('artikel.show', $articleId) }}"
        @endif
        data-image-max-bytes="{{ $imageLimit }}"
        data-error-request-size="{{ __('article.form.request_size') }}"
        data-success-create="{{ __('article.form.submitted') }}"
    >
        <header class="article-form-header">
            <div>
                <h1 class="page-title">
                    {{ $isEdit ? __('Edit Artikel') : __('Buat Artikel') }}
                </h1>

                <p>
                    {{ $isEdit
                        ? __('Perbarui isi artikel. Artikel yang diedit akan kembali menunggu moderasi.')
                        : __('Tulis artikel untuk dibagikan kepada komunitas Anufish.')
                    }}
                </p>
            </div>
        </header>

        <form
            class="article-form"
            method="POST"
            action="{{ $isEdit ? route('artikel.update', $articleId) : route('artikel.store') }}"
            data-article-form-element
            enctype="multipart/form-data"
            novalidate
        >
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif
            <div class="article-form-field">
                <label for="judul">
                    {{ __('Judul') }}
                </label>

                <input
                    id="judul"
                    name="judul"
                    type="text"
                    maxlength="255"
                    autocomplete="off"
                    required
                >

                <p
                    class="article-field-error"
                    data-error-for="judul"
                    hidden
                ></p>
            </div>

            <div class="article-form-field">
                <label for="kategori">
                    {{ __('Kategori') }}
                </label>

                <input
                    id="kategori"
                    name="kategori"
                    type="text"
                    maxlength="100"
                    autocomplete="off"
                    required
                >

                <p
                    class="article-field-error"
                    data-error-for="kategori"
                    hidden
                ></p>
            </div>

            <div class="article-form-field">
                <label for="isi">
                    {{ __('Isi Artikel') }}
                </label>

                <textarea
                    id="isi"
                    name="isi"
                    rows="12"
                    maxlength="50000"
                    required
                ></textarea>

                <p
                    class="article-field-error"
                    data-error-for="isi"
                    hidden
                ></p>
            </div>

            <div class="article-form-field">
                <label for="gambar">
                    {{ __('Gambar Artikel') }}
                </label>

                <input
                    id="gambar"
                    name="gambar"
                    type="file"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                    data-article-image-input
                    aria-describedby="article-image-help"
                >

                <p id="article-image-help" class="article-form-help">
                    {{ __('article.form.image_help', ['limit' => $imageLimitLabel]) }}
                </p>

                <p
                    class="article-field-error"
                    data-error-for="gambar"
                    hidden
                ></p>

                <button type="button" class="article-secondary-action" data-article-image-clear hidden>
                    {{ __('article.form.clear_image_selection') }}
                </button>

                <div
                    class="article-image-preview"
                    data-article-image-preview
                    hidden
                >
                    <img
                        data-article-image-preview-image
                        alt="{{ __('Pratinjau gambar artikel') }}"
                    >
                </div>
            </div>

            <div
                class="article-form-message"
                data-article-form-message
                hidden
            >
                <p data-article-form-message-text role="status"></p>
                <a href="{{ route('artikel-saya.index') }}" class="mt-2 inline-flex min-h-11 items-center underline" data-article-form-success-link hidden>
                    {{ __('article.form.view_my_articles') }}
                </a>
            </div>

            <div class="article-form-actions">
                <a
                    href="{{ route('artikel-saya.index') }}"
                    class="article-secondary-action"
                >
                    {{ __('Batal') }}
                </a>

                <button
                    type="submit"
                    class="primary-action"
                    data-article-submit
                    @disabled($isEdit)
                >
                    <span data-submit-label>
                        {{ $isEdit ? __('Simpan Perubahan') : __('Buat Artikel') }}
                    </span>

                    <span
                        data-submit-loading
                        hidden
                    >
                        {{ __('Menyimpan...') }}
                    </span>
                </button>
            </div>
        </form>
    </div>
@endsection
