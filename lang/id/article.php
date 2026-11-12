
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

    'moderation' => [
        'approved' => 'Artikel berhasil disetujui.',
        'rejected' => 'Artikel berhasil ditolak.',
        'deleted' => 'Artikel berhasil dihapus.',
        'conflict' => 'Artikel sudah berubah atau tindakan ini sudah tidak tersedia. Buka ulang detail artikel.',
        'failed' => 'Gagal memperbarui status moderasi. Silakan coba lagi.',
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
        'title' => 'Artikel',
        'description' => 'Jelajahi artikel yang telah dipublikasikan.',
        'empty_title' => 'Belum ada artikel',
        'empty_description' => 'Artikel yang telah disetujui akan ditampilkan di sini.',
        'no_image' => 'Gambar tidak tersedia',
        'author' => 'Penulis',
        'unknown_author' => 'Penulis tidak diketahui',
        'pagination' => 'Navigasi halaman artikel',
    ],

    'admin_moderation' => [
        'list_title' => 'Moderasi Artikel',
        'list_description' => 'Kelola dan tinjau artikel yang dikirim pengguna.',
        'detail_title' => 'Detail Moderasi Artikel',

        'filter_status' => 'Filter Status',
        'all_statuses' => 'Semua Status',
        'apply_filter' => 'Terapkan Filter',

        'author' => 'Penulis',
        'unknown_author' => 'Penulis tidak diketahui',
        'category' => 'Kategori',
        'created_at' => 'Tanggal Dibuat',
        'content' => 'Isi Artikel',

        'view_detail' => 'Lihat Detail',
        'back_to_list' => 'Kembali ke Daftar',

        'empty' => 'Tidak ada artikel dengan status ini.',
        'pagination' => 'Navigasi halaman artikel',

        'actions_title' => 'Aksi Moderasi',
        'actions_pending' => 'Kontrol moderasi akan dihubungkan ke backend pada tahap integrasi.',

        // Tombol dan pesan aksi moderasi
        'approve' => 'Setujui',
        'reject' => 'Tolak',
        'delete' => 'Hapus',
        'confirm_delete' => 'Apakah kamu yakin ingin menghapus artikel ini?',
        'refresh_detail' => 'Muat ulang detail artikel',
        'request_failed' => 'Terjadi kesalahan. Silakan coba lagi.',

        'statuses' => [
            'menunggu' => 'Menunggu',
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
        ],
    ],
];
