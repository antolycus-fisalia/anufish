<?php

return [
    'required' => ':attribute wajib diisi.',
    'string' => ':attribute harus berupa teks.',
    'email' => ':attribute harus berupa alamat email yang valid.',
    'integer' => ':attribute harus berupa bilangan bulat.',
    'in' => 'Pilihan :attribute tidak valid.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'unique' => ':attribute sudah digunakan.',
    'image' => ':attribute harus berupa gambar.',
    'mimes' => ':attribute harus berformat :values.',
    'uploaded' => ':attribute gagal diunggah.',
    'min' => ['string' => ':attribute minimal :min karakter.', 'numeric' => ':attribute minimal :min.', 'file' => ':attribute minimal :min kilobyte.', 'array' => ':attribute minimal :min item.'],
    'max' => ['string' => ':attribute maksimal :max karakter.', 'numeric' => ':attribute maksimal :max.', 'file' => ':attribute maksimal :max kilobyte.', 'array' => ':attribute maksimal :max item.'],
    'attributes' => ['q' => 'Kata pencarian', 'page' => 'Halaman', 'locale' => 'Bahasa', 'name' => 'Nama', 'email' => 'Email', 'password' => 'Password', 'profile_photo' => 'Foto profil'],
];
