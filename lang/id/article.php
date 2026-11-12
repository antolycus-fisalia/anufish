<?php

return [
    'messages' => [
        'forbidden' => 'Kamu tidak diizinkan melakukan tindakan ini pada artikel.',
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
        'image_dimensions' => 'Resolusi gambar artikel maksimal 8 megapiksel.',
        'image_resources' => 'Gambar terlalu besar untuk diproses. Silakan kurangi resolusinya.',
        'image_max' => 'Ukuran gambar maksimal 5 MB.',
        'image_format' => 'Format gambar harus JPG, JPEG, atau PNG.',
    ],

    'public' => [
        'back_to_list' => 'Kembali ke daftar artikel',
        'author' => 'Penulis',
        'unknown_author' => 'Penulis tidak diketahui',
    ],
];
