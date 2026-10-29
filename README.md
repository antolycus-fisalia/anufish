<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Pencarian dan detail ikan iNaturalist

Beranda (`/`) menampilkan hero, pintasan dan bagian bacaan. Jelajahi Ikan (`/fish`)
menjadi halaman pencarian tersendiri; tautan lama `/?q=...&page=...` diarahkan ke
`/fish` dengan query dan halaman yang sama. Endpoint JSON `/fish/search` tetap
tersedia. Scan, riwayat dan artikel masih ditandai Segera sampai fiturnya selesai.

`InaturalistService` memakai API publik iNaturalist sebagai satu-satunya sumber
referensi takson dan foto. Input nama ilmiah/umum menerima Unicode 1–150 karakter,
termasuk CJK dan RTL. Nama yang ditampilkan hanya Indonesia dan Inggris. Nama yang
cocok tepat diprioritaskan, lalu bahasa pilihan pengguna. Toggle ID/EN menyimpan
pilihan dalam session dan mempertahankan URL pencarian. Nama tidak diterjemahkan
atau dibuat sendiri. Pagination mengikuti
`page`, `per_page`, dan `total_results` provider; halaman tanpa ikan masih dapat
menawarkan halaman berikutnya karena hasil non-ikan disaring.

Setelah enrichment, hasil juga harus cocok dengan nama ilmiah atau nama umum yang
valid berbahasa Indonesia/Inggris. Kecocokan sebagian nama tidak membedakan huruf
besar/kecil dan memakai normalisasi Unicode yang sama. Nama dari bahasa lain,
misalnya Māori `tuna` untuk belut, tidak boleh menjadi alasan lolosnya hasil.
`preferred_common_name` tanpa bukti bahasa tidak dipakai untuk filter pencarian.

Filter ancestry mencakup Actinopterygii (47178), Chondrichthyes (196614),
Myxini (49099), Petromyzonti (49231), Ceratodontiformes (85509), dan
Coelacanthiformes (85511). Sarcopterygii umum tidak cukup untuk filter karena juga
mencakup tetrapoda. Endpoint pencarian `/taxa` diikuti satu batch `/taxa/{ids}`
untuk melengkapi ancestors dan foto tanpa request per kartu. Enrichment yang gagal
tetap mempertahankan identitas hasil pencarian. Tidak ada master ikan permanen lokal.

Konfigurasi opsional: `INATURALIST_BASE_URL`, `INATURALIST_TIMEOUT` (maksimal 4 detik),
`INATURALIST_CACHE_MINUTES` (default 15), `INATURALIST_SEARCH_CACHE_MINUTES`
(default 30), dan `INATURALIST_CA_BUNDLE`. Verifikasi TLS
selalu aktif. Bundle Mozilla berada di `resources/certificates/cacert.pem`; perbarui
dari [sumber resmi curl](https://curl.se/docs/caextract.html) dalam pemeliharaan rutin.
Cache production harus dibagi antar worker/instance (database atau Redis dengan
namespace yang sama). Hasil akhir pencarian yang sudah difilter disimpan 30 menit,
berdasarkan kata kunci yang dinormalisasi, bahasa, halaman, jumlah hasil, dan URL API.
Lock per pencarian membuat request bersamaan menunggu cache yang sama dalam
deadline pencarian; jika waktu tunggu habis, respons 429 dikembalikan tanpa request
API duplikat. Kegagalan API atau enrichment tidak disimpan sebagai hasil akhir.
Filter nama Indonesia, Inggris, dan ilmiah tetap diterapkan sebelum cache disimpan.
Limiter bersama membatasi 60 request dalam 60 detik dan
9.999 request per hari untuk semua panggilan iNaturalist. Cache hit tidak memakai
kuota keluar. `Retry-After` 429 menghasilkan cooldown bersama; tidak ada retry
otomatis yang memperbesar beban. Deadline setiap request pencarian, detail utama,
atau enrichment maksimal 4 detik. Enrichment detail dimuat terpisah melalui
`/fish/taxa/{taxonId}/extras` agar tidak menunda halaman utama.

Kartu membuka `/fish/taxa/{taxonId}` (login diperlukan), mengambil `/taxa/{id}`.
ID adalah taxon ID iNaturalist, bukan ID dari sumber sebelumnya; tautan/bookmark
lama dengan pola /fish/{id} mendapat 404 agar ID lama tidak ditafsirkan sebagai takson lain. Kembali ke hasil
mempertahankan query dan halaman. 404, 429 dan gangguan 502 mempunyai state yang
berbeda. Nama/taksonomi/foto yang tidak tersedia dijelaskan tanpa mengarang data.

Foto dari `default_photo` atau `taxon_photos` untuk takson yang sama. Bila kosong,
enrichment mencari satu pengamatan research grade dari takson yang persis sama.
Foto medium dipakai agar ringan, dengan
lisensi CC0, CC BY atau CC BY-SA serta kredit fotografer, sumber dan tautan lisensi.
Detail menampilkan seluruh foto pilihan takson yang lolos filter dalam galeri.
Thumbnail memakai ukuran square dan lazy loading; foto utama, kredit, dan lisensi
mengikuti pilihan pengguna. Endpoint terautentikasi `/fish/taxa/{taxonId}/photos`
memuat foto pengamatan tambahan sesuai permintaan, 12 pengamatan per batch dengan
cursor `before` (`id_below`, urutan ID menurun), sehingga tidak mengunduh semua foto
sekaligus. Setiap foto harus berasal dari takson yang sama, berlisensi yang didukung,
tidak disembunyikan, dan memiliki atribusi. Duplikat dihapus; lokasi tidak dikirim ke
browser. Galeri menyediakan keyboard, loading, retry dan keadaan akhir ID/EN.
Respons API foto memakai cache dan anggaran request bersama yang sudah tersedia.
Klik foto utama membuka dialog foto besar dengan tombol sebelumnya/berikutnya,
panah keyboard dan Escape. Pilihan foto tetap tersinkron dengan galeri, termasuk
foto tambahan yang sudah dimuat. Dialog menampilkan kredit dan lisensi, menahan
fokus serta scroll halaman di belakangnya, lalu mengembalikan fokus saat ditutup.
Foto large hanya dimuat sesuai pilihan; jika gagal, gunakan medium lalu tampilkan
error/retry bila keduanya gagal. Markup berada di `x-fish.photo-viewer`, CSS di
`resources/css/fish-photo-viewer.css`, dan perilaku di `resources/js/fish-photo-viewer.js`.
Lisensi terbatas/tidak diketahui dan URL non-HTTPS ditolak; kegagalan gambar memakai
placeholder. Foto tidak disalin permanen ke aplikasi dan cakupannya tidak dijamin.

Ringkasan Wikipedia hanya ditampilkan bila disediakan melalui respons iNaturalist,
sebagai teks biasa. Bila versi Indonesia kosong, enrichment mengambil versi Inggris
dan memberi catatan fallback. Habitat tidak ditebak dari ringkasan. Lokasi pengamatan
tidak ditampilkan dan tidak ada request observasi untuk informasi lokasi. Kegagalan
foto atau deskripsi tambahan tidak menghilangkan detail takson. Cache respons
dipisahkan menurut bahasa; seluruh panggilan tetap memakai limiter bersama.

Gemini/scan dan riwayat scan belum tersedia di kode proyek ini. Integrasi iNaturalist
ini tidak menambahkan layanan identifikasi gambar. Saat fitur itu diimplementasikan,
Gemini tetap menghasilkan kandidat dan confidence, sedangkan referensi riwayat memakai
`inaturalist_taxon_id BIGINT NULL` setelah kecocokan nama ilmiah tervalidasi.

Referensi: [dokumentasi API iNaturalist](https://api.inaturalist.org/v1/docs/).

### Aset frontend

CSS berada di `resources/css/app.css` dan `resources/css/guest.css`.
JavaScript dimuat lewat Vite dari `resources/js/app.js`, dengan modul `ui.js`,
`fish-detail.js`, dan `auth-layout.js`. Blade menyimpan markup, referensi class,
dan atribut `data-*` untuk data dari Laravel; tidak ada CSS atau JavaScript inline.
Class Tailwind dikompilasi menjadi stylesheet eksternal saat `npm run build`.

### Pembagian service dan model

`InaturalistClient` menangani HTTP, cache, limiter bersama, cooldown dan timeout.
`InaturalistTaxonMapper` menangani filter ikan, nama ID/EN, taksonomi, sanitasi
teks dan foto berlisensi. `InaturalistService` mengatur pencarian serta pengambilan
takson; `FishDetailService` mengatur detail dan fallback deskripsi Inggris.

Respons extras mempertahankan `status: 200` untuk data parsial, dengan
`image_status` dan `description_status`: 200 tersedia, 404 tidak tersedia,
429 terkena pembatasan, atau 502 gagal mengambil data. Kegagalan tambahan
menampilkan pesan gagal dan tindakan coba lagi tanpa menyembunyikan detail utama.
`User::isAdmin()` dan `User::isActive()` menyatukan pemeriksaan role/status;
password registrasi di-hash oleh cast `hashed` pada model.

### Identifikasi foto melalui Ruvicode

Endpoint `POST /scan/upload` menyimpan foto pada disk privat, kemudian mengirim
gambar JPEG/PNG sebagai base64 ke API chat completions Ruvicode. Konfigurasi server:

```dotenv
RUVICODE_BASE_URL=https://api.ruvicode.com/v1
RUVICODE_API_KEY=
RUVICODE_MODEL=gemini-3-5-flash
RUVICODE_TIMEOUT=45
```

Isi key pribadi di `.env`; jangan masukkan ke variabel `VITE_*` atau repository.
Pilih ID model yang mendukung **vision** dari katalog Ruvicode. `gemini-3-5-flash`
sudah diuji melalui Ruvicode pada foto ikan. Model `kimi-k3` yang semula dipakai
berhasil pada foto contoh tetapi timeout pada foto hiu yang sama dengan laporan
pengguna. Ketersediaan model dan akses key perlu diverifikasi pada akun yang
digunakan; hasil waktu respons dapat berubah. Setelah mengubah `.env`,
jalankan `php artisan config:clear`. Jika aplikasi dijalankan dengan `composer run dev`
atau `php artisan dev`, hentikan seluruh proses dev tersebut dengan Ctrl+C lalu
jalankan ulang `composer run dev`. Launcher dev dapat mewariskan nilai `.env` lama
ke server anak; membersihkan config cache saja tidak mengganti environment proses
yang sudah berjalan. Tidak ada request berbayar otomatis saat setup.

Respons sukses mempertahankan status **201** dan metadata upload sebelumnya,
ditambah `data.candidates` berisi `scientific_name` dan `confidence` (0–1 atau null).
Daftar kosong berarti ikan tidak dikenali. Skor AI bukan verifikasi identitas ikan.
Kegagalan konfigurasi, provider, timeout, file, atau respons malformed menghasilkan
**502** dengan pesan ID/EN; aplikasi mencoba menghapus foto upload saat identifikasi gagal.
Tidak ada retry provider otomatis agar satu upload tidak menimbulkan tagihan ganda.
Foto di atas 256 KB dikirim sebagai salinan JPEG kualitas 80 dengan sisi terpanjang
maksimal 1024 px; foto asli tidak diubah. Pemrosesan ini membutuhkan ekstensi GD.
Foto besar dengan lebih dari 16 juta piksel ditolak sebelum decoding untuk membatasi
penggunaan memori. Timeout Ruvicode dapat diatur 1–60 detik; nilai awal 45 detik
karena pengujian foto live melampaui 10 detik. Ini menggantikan batas lama integrasi
Gemini langsung. Request yang timeout tidak berarti provider pasti berhenti memproses.
Upload hanya mengembalikan `scientific_name` dan `confidence` asli dari AI;
tidak ada pencarian iNaturalist saat upload. Semua hasil, termasuk satu kandidat,
menunggu pengguna menekan Pilih kandidat.
Referensi spesies/foto menggunakan iNaturalist melalui FSH-15. Setelah kandidat
dipilih, hasil referensi menambahkan `match_status`
(`matched`, `ambiguous`, `unavailable`), `taxon_id`, `reference`,
`reference_options`, dan `reference_http_status`.
Pencocokan hanya menerima nama ilmiah tepat pada spesies aktif dalam kelompok
ikan, termasuk hiu. Nama mirip tidak dipilih otomatis. Hasil ambigu memiliki
`taxon_id: null`; pengguna memilih referensi dari opsi tervalidasi. Pilihan UI
belum disimpan: konsumen riwayat harus memvalidasi kembali pilihan di server.
Jika iNaturalist gagal atau membatasi request, kandidat AI tetap terlihat
dan UI menampilkan penyebab kegagalan serta tombol retry. Referensi menggunakan
limiter/cache yang sama dengan pencarian. Foto hanya
ditampilkan bila lisensi dan atribusinya sesuai. Tidak ada master ikan permanen
atau penyimpanan riwayat baru dalam FSH-15.

Saat kandidat dipilih, UI memanggil `GET /scan/reference?scientific_name=...`
(auth, throttle 30/menit). Endpoint memvalidasi nama wajib/string/maksimal 200
karakter dan mengambil satu referensi dengan deadline tersendiri; cache dan
limiter provider tetap berlaku. Respons 200 berisi `data` dengan kontrak kandidat
di atas tanpa confidence baru. `unavailable` menyertakan `reference_error` ID/EN
dan `reference_http_status` (404 tidak cocok, 429 limiter, 502 timeout/koneksi/data
provider gagal). HTTP endpoint: 401 guest, 422 input tidak valid, 429 throttle.
Tombol retry tidak mengulang analisis Ruvicode. Respons pilihan lama diabaikan.
Jika matched, UI langsung membuka route `fish.show` untuk taxon ID yang sudah
tervalidasi. Jika ambiguous, pengguna memilih salah satu opsi referensi sebelum
halaman detail dibuka. Kegagalan tetap berada di halaman scan.
Tidak ada migrasi. FSH-17 tetap wajib memvalidasi ulang identitas sebelum menyimpan.

Dokumentasi API: https://ruvicode.com/docs/chat-completions.

### Seed admin

Tambahkan `ADMIN_NAME`, `ADMIN_EMAIL`, dan `ADMIN_PASSWORD` ke `.env` sesuai
`.env.example`. Password wajib diisi, minimal 8 karakter; tidak ada password default.
Jalankan `php artisan config:clear`, kemudian
`php artisan db:seed --class=AdminSeeder`. `php artisan db:seed` juga memanggil
AdminSeeder melalui DatabaseSeeder.

Admin baru memiliki role `admin` dan status `aktif`; hashing ditangani model User.
Seed berulang mempertahankan password, nama dan status akun yang sudah ada.
Email yang dipakai akun non-admin ditolak tanpa menaikkan hak aksesnya.
