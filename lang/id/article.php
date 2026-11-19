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

    'notification' => [
        'reload' => 'Muat ulang',
        'unread_count' => ':count belum dibaca',
        'title' => 'Notifikasi Moderasi',
        'description' => 'Pembaruan status artikel kamu dari moderator.',
        'mark_all_read' => 'Tandai semua dibaca',
        'mark_read' => 'Tandai dibaca',
        'load_failed' => 'Notifikasi belum dapat dimuat. Silakan coba lagi.',
        'mark_failed' => 'Notifikasi belum dapat ditandai dibaca. Silakan coba lagi.',
        'empty_title' => 'Belum ada notifikasi',
        'empty_message' => 'Kamu akan diberi tahu di sini ketika status artikel kamu berubah.',
        'message_approved' => 'Artikel ":title" disetujui oleh moderator.',
        'message_rejected' => 'Artikel ":title" ditolak oleh moderator.',
        'message_deleted' => 'Artikel ":title" dihapus oleh moderator.',
        'view_article' => 'Buka artikel',
        'link_missing' => 'Artikel tidak lagi tersedia.',
        'not_found' => 'Notifikasi tidak ditemukan.',
        'marked' => 'Notifikasi ditandai dibaca.',
        'marked_all' => 'Semua notifikasi ditandai dibaca.',
        'status_approved' => 'Disetujui',
        'status_rejected' => 'Ditolak',
        'status_deleted' => 'Dihapus',
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
        'image_server_max' => 'Ukuran gambar melebihi batas unggahan server. Coba gambar yang lebih kecil.',
        'image_upload_failed' => 'Gambar artikel gagal diunggah. Silakan pilih ulang gambar.',
        'image_format' => 'Format gambar harus JPG, JPEG, PNG, atau WebP.',
    ],

    'form' => [
        'image_help' => 'Format JPG, JPEG, PNG, atau WebP. Maksimal :limit.',
        'image_max' => 'Ukuran gambar maksimal :limit.',
        'request_size' => 'Kiriman melebihi batas ukuran server. Coba gambar yang lebih kecil.',
        'clear_image_selection' => 'Hapus pilihan gambar',
        'view_my_articles' => 'Lihat Artikel Saya',
        'submitted' => 'Artikel berhasil dibuat dan menunggu moderasi.',
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
        'back_to_list' => 'Kembali ke daftar artikel',
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

        'actions_title' => 'Aksi Moderasi',
        'actions_pending' => 'Kontrol moderasi akan dihubungkan ke backend pada tahap integrasi.',

        // Tombol dan pesan aksi moderasi
        'approve' => 'Setujui',
        'reject' => 'Tolak',
        'delete' => 'Hapus',
        'confirm_delete' => 'Apakah kamu yakin ingin menghapus artikel ini?',
        'refresh_detail' => 'Muat ulang detail artikel',
        'article_unavailable' => 'Artikel sudah dihapus atau tidak lagi tersedia.',
        'request_failed' => 'Terjadi kesalahan. Silakan coba lagi.',

        'statuses' => [
            'menunggu' => 'Menunggu',
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
        ],
    ],
];
