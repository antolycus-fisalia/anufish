# Anufish

Aplikasi untuk mencari dan mengidentifikasi ikan dari foto, menyimpan riwayat scan, serta membaca dan memoderasi artikel komunitas. Data spesies berasal dari iNaturalist, dan identifikasi foto menggunakan Ruvicode.

[Health check dan pemantauan uptime](docs/AVAILABILITY.md).

## Jalankan secara lokal

Butuh PHP 8.3+ dengan ekstensi GD dan PDO MySQL, Composer, Node.js 22.12+, dan MySQL.

Salin `.env.example` menjadi `.env`, buat database MySQL, lalu sesuaikan `DB_*`. Isi `RUVICODE_API_KEY` di `.env` untuk menggunakan fitur scan.

```bash
composer setup
php artisan storage:link
php artisan serve
```

Buka [localhost:8000](http://localhost:8000).

## Akun dari seeder

| Akun | Email | Password |
| --- | --- | --- |
| Admin | `admin@anufish.test` (default) | `ADMIN_PASSWORD` di `.env` |
| Penulis demo | `penulis-demo@anufish.test` | `anufish-demo-local` |

Isi `ADMIN_PASSWORD` dengan minimal 8 karakter sebelum seed admin. Seed penulis dan artikel demo tersedia di `local` atau `testing`.

```bash
php artisan db:seed --class=AdminSeeder
php artisan db:seed --class=ArticleDemoSeeder
```

## Pengujian

Tes Laravel menggunakan SQLite in-memory (`pdo_sqlite`).

```bash
php artisan test
node --test tests/Frontend/*.test.js
```
