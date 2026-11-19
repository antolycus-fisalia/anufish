# API Anufish v1

Base URL lokal: `http://localhost:8000/api/v1`. API mencakup fitur website: akun, profil, pencarian ikan, scan, riwayat, artikel, notifikasi, dan administrasi.

## Mulai

Untuk instalasi yang sudah ada, jalankan:

```bash
composer install
php artisan migrate
php artisan storage:link
php artisan serve
```

Untuk instalasi baru, ikuti [README](../README.md). Konfigurasi scan tetap di `.env`: `NINEROUTER_BASE_URL` termasuk `/v1`, `NINEROUTER_API_KEY`, dan `NINEROUTER_MODEL` yang mendukung gambar. Server Laravel perlu dapat menjangkau server 9Router; kunci 9Router tetap di server.

Import [koleksi Postman](anufish.postman_collection.json), lalu isi variabel `email` dan `password`. Jalankan **Login** untuk menyimpan token otomatis di variabel koleksi. Isi variabel ID sesuai data yang ingin diuji. Koleksi hanya mengirim permintaan saat dijalankan; sebagian permintaan mengubah atau menghapus data.

## Autentikasi

Daftar melalui `POST /auth/register` dengan `name`, `email`, `password`, dan `password_confirmation`. Password minimal 8 karakter. Akun baru selalu berperan `user` dan berstatus `aktif`. Registrasi mengembalikan akun dengan HTTP 201; selanjutnya login.

`POST /auth/login` menerima JSON:

```json
{"email":"pengguna@example.com","password":"password-anda"}
```

Respons login berisi `data` pengguna, `token_type: "Bearer"`, `access_token`, dan `expires_at`. Token berlaku 30 hari. Kirim pada setiap endpoint terlindungi:

```http
Authorization: Bearer TOKEN_DARI_LOGIN
Accept: application/json
Accept-Language: id
```

Pilihan bahasa: `id` atau `en`, default `id`. API memakai token, tanpa cookie login atau CSRF. `POST /auth/logout` mencabut token yang digunakan saat itu; token perangkat lain tetap berlaku. Login kembali setelah token kedaluwarsa. Gunakan HTTPS saat dipasang di server.

## Endpoint

Semua path berikut relatif terhadap base URL. Selain health check, registrasi, dan login, endpoint memerlukan token pengguna aktif. Endpoint `/admin/*` memerlukan peran `admin`.

| Metode | Path | Input / kegunaan |
| --- | --- | --- |
| GET | `/up` | Liveness |
| GET | `/ready` | Kesiapan database |
| POST | `/auth/register` | `name`, `email`, `password`, `password_confirmation` |
| POST | `/auth/login` | `email`, `password` |
| GET | `/auth/me` | Pengguna yang login |
| POST | `/auth/logout` | Cabut token saat ini |
| GET | `/home` | Artikel terbaru yang disetujui |
| GET | `/profile` | Profil dan `photo_url` |
| PUT | `/profile` | `name`, `email`, opsional file `profile_photo` |
| GET | `/fish` | Opsional `q`, `page`; kosong jika tanpa pencarian |
| GET | `/fish/search` | Wajib `q`; opsional `page` (1–5000) |
| GET | `/fish/taxa/{taxonId}` | Detail ikan; opsional `q` untuk nama umum |
| GET | `/fish/taxa/{taxonId}/extras` | Informasi tambahan |
| GET | `/fish/taxa/{taxonId}/photos` | Foto berlisensi; opsional cursor `before` |
| POST | `/scan/upload` | File `fish_image`, opsional UUID `operation_id` |
| GET | `/scan/reference` | Wajib `scientific_name` |
| GET | `/scan/history` | Riwayat milik sendiri; opsional `page` |
| GET | `/scan/history/{history}` | Detail dan `image_url` |
| GET | `/scan/history/{history}/image` | Isi file gambar privat, tetap memerlukan token |
| POST | `/scan/history/{history}/reference` | `candidate_index`, indeks kandidat mulai 0 |
| GET | `/articles` | Artikel yang disetujui; opsional `page` |
| GET | `/articles/{artikel}` | Detail artikel yang disetujui |
| GET | `/my/articles` | Artikel sendiri; opsional `page` |
| POST | `/my/articles` | `judul`, `kategori`, `isi`, opsional file `gambar` |
| GET | `/my/articles/{artikel}` | Detail artikel sendiri; admin juga dapat melihat |
| PUT | `/my/articles/{artikel}` | `judul`, `kategori`, `isi`, opsional file `gambar` |
| DELETE | `/my/articles/{artikel}` | Hapus artikel sendiri |
| GET | `/notifications` | Maksimal 20 notifikasi moderasi dan `unread_count` |
| POST | `/notifications/read-all` | Tandai seluruh notifikasi moderasi dibaca |
| POST | `/notifications/{notificationId}/read` | Tandai satu notifikasi dibaca; ID berupa UUID |
| GET | `/admin/dashboard` | Statistik dan antrean artikel |
| GET | `/admin/users` | Opsional `search`, `page` |
| GET | `/admin/users/{user}` | Detail pengguna |
| PUT | `/admin/users/{user}` | `name`, `email` |
| GET | `/admin/articles` | Opsional `status`, `page`; default `menunggu` |
| GET | `/admin/articles/{artikel}` | Detail beserta `moderation_revision` |
| POST | `/admin/articles/{artikel}/approve` | `moderation_revision` |
| POST | `/admin/articles/{artikel}/reject` | `moderation_revision` |
| POST | `/admin/articles/{artikel}/delete` | `moderation_revision` |

## Upload dan alur data

Upload menggunakan `multipart/form-data`, menerima JPG/JPEG, PNG, dan WebP maksimal 5 MB per file, mengikuti batas upload PHP jika lebih kecil. Foto profil minimal 32 × 32 piksel. Gambar artikel **opsional**. Untuk mengganti file lewat endpoint PUT, kirim **POST multipart** ke path yang sama dengan field `_method=PUT`, agar file dapat diproses PHP.

Artikel memerlukan judul maksimal 255 karakter, kategori maksimal 100 karakter, dan isi maksimal 50.000 karakter. HTML disanitasi dan harus berisi teks. Membuat atau mengedit artikel menetapkan status `menunggu`, termasuk jika sebelumnya disetujui. Role, status, dan pemilik tidak dapat diganti lewat payload artikel/profil. Admin mengedit nama dan email pengguna sesuai fitur website yang tersedia.

Scan mengembalikan `candidates`, `history_id`, dan `history_url` di dalam `data`. Jika ikan tidak dikenali, kandidat kosong dan tidak ada riwayat baru. Gunakan UUID `operation_id` yang sama untuk mengulangi upload foto yang sama tanpa membuat riwayat ganda. UUID sama dengan foto berbeda menghasilkan 409. Referensi iNaturalist melaporkan `match_status`: `matched`, `ambiguous`, atau `unavailable`. Unduh gambar riwayat menggunakan `image_url` beserta header Bearer; path penyimpanan bukan URL publik.

Moderasi memakai `moderation_revision` dari detail admin terbaru. Setelah tindakan berhasil, gunakan revisi baru dari respons untuk tindakan berikutnya. Revisi lama menghasilkan 409; ambil detail terbaru sebelum mencoba lagi. Filter status menerima `menunggu`, `disetujui`, `ditolak`, atau `status=` untuk semua status. Artikel yang dihapus tidak muncul lagi.

## Respons dan batas permintaan

Detail umumnya memakai `{"data": {...}}`. Daftar artikel/pengguna memiliki `data` dan pagination (`current_page`, `last_page`, `total`). Daftar riwayat memakai `data`, `links`, dan `meta`. Pencarian ikan mempertahankan format layanan: `success`, `data`, `pagination` bila tersedia. Gambar riwayat mengembalikan file, bukan JSON. Jangan bergantung pada teks `message` karena mengikuti bahasa permintaan.

| HTTP | Arti |
| --- | --- |
| 200 / 201 | Berhasil / berhasil dibuat |
| 401 | Token tidak ada, tidak valid, dicabut, atau kedaluwarsa |
| 403 | Akun tidak aktif atau tidak berhak melakukan tindakan |
| 404 | Data tidak ditemukan atau riwayat bukan milik pengguna |
| 409 | Konflik revisi moderasi atau operasi scan |
| 422 | Validasi; rincian field tersedia di `errors` |
| 429 | Batas permintaan tercapai; ikuti `Retry-After` |
| 500 / 502 / 503 | Gagal menyimpan, gangguan layanan eksternal, atau database belum siap |

Endpoint terlindungi dibatasi 60 permintaan/menit per pengguna (dibagi antar token). Registrasi 5/menit per IP; login 5/menit per kombinasi email dan IP. Endpoint referensi scan juga memiliki batas 30/menit. API mengembalikan JSON termasuk error meskipun header `Accept` tidak diberikan.

## Pengujian

```bash
php artisan test --filter=Api
php artisan test
```

Tes API memakai SQLite terisolasi dan mock layanan eksternal, mencakup autentikasi, kedaluwarsa/pencabutan token, pembatasan akses, WebP, CRUD, riwayat privat, notifikasi, dan konflik moderasi. Koneksi 9Router yang sesungguhnya memerlukan konfigurasi `.env` yang valid.
