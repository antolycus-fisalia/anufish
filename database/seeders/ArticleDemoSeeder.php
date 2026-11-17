<?php

namespace Database\Seeders;

use App\Models\Artikel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ArticleDemoSeeder extends Seeder
{
    private const EMAIL = 'penulis-demo@anufish.test';

    private const ARTICLES = [
        [
            'judul' => 'Demo: Mengenal Ikan Badut',
            'kategori' => 'Ikan Laut',
            'isi' => '<p>Ikan badut hidup di terumbu karang dan berlindung di antara tentakel anemon.</p>',
            'status' => 'disetujui',
        ],
        [
            'judul' => 'Demo: Mengamati Habitat Ikan Cupang',
            'kategori' => 'Ikan Air Tawar',
            'isi' => '<p>Ikan cupang dapat ditemukan di perairan dangkal yang memiliki banyak tumbuhan air.</p>',
            'status' => 'menunggu',
        ],
        [
            'judul' => 'Demo: Mencatat Pengamatan Ikan',
            'kategori' => 'Panduan',
            'isi' => '<p>Catat lokasi, waktu, dan ciri ikan agar hasil pengamatan mudah diperiksa kembali.</p>',
            'status' => 'menunggu',
        ],
        [
            'judul' => 'Demo: Memeriksa Sumber Identifikasi',
            'kategori' => 'Panduan',
            'isi' => '<p>Bandingkan ciri ikan dengan referensi yang mencantumkan nama ilmiah dan lokasi pengamatan.</p>',
            'status' => 'ditolak',
        ],
    ];

    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            throw new RuntimeException('ArticleDemoSeeder hanya dapat dijalankan pada lingkungan local atau testing.');
        }

        DB::transaction(function () {
            $author = User::firstOrCreate(['email' => self::EMAIL], [
                'name' => 'Penulis Demo Anufish',
                'password' => 'anufish-demo-local',
                'role' => 'user',
                'status' => 'aktif',
            ]);

            // Serialisasi seed untuk penulis yang sama karena judul artikel tidak unik.
            $author = User::query()->lockForUpdate()->findOrFail($author->getKey());

            if ($author->role !== 'user') {
                throw new RuntimeException('Email penulis demo sudah digunakan akun dengan role berbeda.');
            }

            foreach (self::ARTICLES as $fixture) {
                $article = Artikel::withTrashed()
                    ->where('user_id', $author->getKey())
                    ->lockForUpdate()
                    ->firstOrNew(['judul' => $fixture['judul']]);

                if ($article->exists) {
                    continue;
                }

                $article->user()->associate($author);
                $article->fill([
                    'kategori' => $fixture['kategori'],
                    'isi' => $fixture['isi'],
                ]);
                $article->status = $fixture['status'];
                if (! $article->save()) {
                    throw new RuntimeException('Artikel demo gagal disimpan.');
                }
            }
        }, attempts: 3);
    }
}
