<?php

return [
    // FSH-25: Daftar dan detail pengguna
    'list_title' => 'Kelola Pengguna',
    'list_description' => 'Lihat dan kelola akun pengguna yang terdaftar.',
    'detail_title' => 'Detail Pengguna',
    'search' => 'Cari berdasarkan nama atau email',
    'search_button' => 'Cari',
    'view_detail' => 'Lihat detail',
    'back_to_list' => 'Kembali ke daftar pengguna',
    'empty' => 'Pengguna tidak ditemukan.',
    'pagination' => 'Navigasi halaman daftar pengguna',
    'name' => 'Nama pengguna',
    'email' => 'Email',
    'role' => 'Peran',
    'status' => 'Status',
    'joined_at' => 'Tanggal bergabung',

    // Existing: Edit pengguna
    'edit_title' => 'Edit pengguna',
    'edit_description' => 'Perbarui nama dan email akun pengguna.',
    'edit_limit' => 'Form ini hanya mengubah nama dan email. Role, status, dan password tetap.',
    'save' => 'Simpan perubahan',
    'saving' => 'Menyimpan...',
    'updated' => 'Data pengguna berhasil diperbarui.',
    'update_failed' => 'Gagal memperbarui data pengguna.',

    'validation' => [
        'name_required' => 'Nama pengguna wajib diisi.',
        'name_string' => 'Nama pengguna harus berupa teks.',
        'name_max' => 'Nama pengguna maksimal 255 karakter.',
        'email_required' => 'Email pengguna wajib diisi.',
        'email_invalid' => 'Format email tidak valid.',
        'email_max' => 'Email maksimal 255 karakter.',
        'email_unique' => 'Email sudah digunakan pengguna lain.',
    ],
];
