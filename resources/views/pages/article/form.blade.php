@extends('layouts.authenticated')

@php
    $isEdit = request()->routeIs('artikel-saya.edit');
    $articleId = $isEdit ? request()->route('artikel') : null;
@endphp

@section('title', $isEdit ? __('Edit Artikel - Anufish') : __('Buat Artikel - Anufish'))

@section('page-content')
    <div
        class="article-form-page"
        data-article-form
        data-mode="{{ $isEdit ? 'edit' : 'create' }}"
        data-error-load="{{ __('Artikel belum dapat dimuat. Silakan coba lagi.') }}"
        data-error-submit="{{ __('Artikel belum dapat disimpan. Silakan coba lagi.') }}"
        data-error-image-format="{{ __('Format gambar harus JPG, JPEG, atau PNG.') }}"
        data-error-image-max="{{ __('Ukuran gambar maksimal adalah 5 MB.') }}"
        data-error-image-invalid="{{ __('File gambar tidak dapat dibaca. Pilih gambar lain yang valid.') }}"
        @if ($articleId)
        data-article-id="{{ $articleId }}"
        @endif
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
            data-article-form-element
            enctype="multipart/form-data"
            novalidate
        >
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
                    accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                    data-article-image-input
                >

                <p class="article-form-help">
                    {{ __('Format JPG, JPEG, atau PNG. Maksimal 5 MB.') }}
                </p>

                <p
                    class="article-field-error"
                    data-error-for="gambar"
                    hidden
                ></p>

                <div
                    class="article-image-preview"
                    data-article-image-preview
                    hidden
                >
                    <img
                        data-article-image-preview-image
                        src=""
                        alt="{{ __('Pratinjau gambar artikel') }}"
                    >
                </div>
            </div>

            <div
                class="article-form-message"
                data-article-form-message
                aria-live="polite"
                hidden
            ></div>

            <div class="article-form-actions">
                <a
                    href="{{ route('homepage') }}"
                    class="article-secondary-action"
                >
                    {{ __('Batal') }}
                </a>

                <button
                    type="submit"
                    class="primary-action"
                    data-article-submit
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
