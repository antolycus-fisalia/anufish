<?php

return [
    'messages' => [
        'created' => 'Artikel berhasil dibuat.',
        'updated' => 'Artikel berhasil diperbarui.',
        'deleted' => 'Artikel berhasil dihapus.',
        'create_failed' => 'Gagal membuat artikel. Silakan coba lagi.',
        'update_failed' => 'Gagal memperbarui artikel. Silakan coba lagi.',
        'delete_failed' => 'Gagal menghapus artikel. Silakan coba lagi.',
    ],
    'validation' => [
        'title_required' => 'Judul artikel wajib diisi.',
        'title_string' => 'Judul artikel harus berupa teks.',
        'title_max' => 'Judul artikel maksimal 255 karakter.',
        'category_required' => 'Kategori artikel wajib diisi.',
        'category_string' => 'Kategori artikel harus berupa teks.',
        'category_max' => 'Kategori artikel maksimal 100 karakter.',
        'content_required' => 'Isi artikel wajib diisi.',
        'content_string' => 'Isi artikel harus berupa teks.',
        'content_max' => 'Isi artikel maksimal 50000 karakter.',
        'image_invalid' => 'Gambar artikel harus berupa file gambar yang valid.',
        'image_max' => 'Ukuran gambar maksimal 5 MB.',
        'image_format' => 'Format gambar harus JPG, JPEG, atau PNG.',
    ],
];
