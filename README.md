# Anufish

Anufish adalah aplikasi web berbasis Laravel. Struktur saat ini menyediakan halaman beranda, tentang, fitur, dan profil pada rute `/`, `/about`, `/feature`, dan `/profile`. Basis datanya juga sudah memiliki struktur untuk artikel dan riwayat pemindaian.

README ini menjadi panduan utama bagi anggota tim baru untuk menyiapkan proyek, memahami bagian frontend, dan mengikuti alur kontribusi.

## Teknologi dan kebutuhan

| Bagian | Paket atau alat | Versi |
| --- | --- | --- |
| Backend | PHP | `^8.3` |
| Backend | Laravel Framework | `^13.17` |
| Backend | Laravel Tinker | `^3.0` |
| Tampilan | Blade, template server-rendered untuk view `.blade.php` | Mengikuti Laravel Framework |
| Tampilan | Blade Components, komponen view yang dapat dipakai ulang di `resources/views/components/` | Mengikuti Laravel Framework |
| Database | SQLite adalah default di `.env.example`; MySQL tersedia dan dapat dikonfigurasi melalui variabel yang dikomentari | Tidak ditetapkan |
| Frontend | Alpine.js | `^3.17.3` |
| Frontend | Tailwind CSS dan plugin Vite | `^4.3.3` |
| Frontend | Vite | `^8.0.0` |
| Integrasi | Laravel Vite Plugin | `^3.1` |
| Alat pengembangan | Concurrently | `^10.0.3` |
| Opsional | Laravel Multiplex | `^0.4.1` |

`composer.json` menerima Laravel `^13.17`, sedangkan `composer.lock` saat ini mengunci Laravel pada `v13.32.0`. Paket pengembangan backend yang tercatat langsung adalah Faker `^1.23`, Pail `^1.2.5`, Pao `^1.0.6`, Pint `^1.27`, Mockery `^1.6`, Collision `^8.6`, dan PHPUnit `^12.5.12`.

Siapkan Git, PHP 8.3 yang memenuhi batas versi di atas, Composer, serta Node.js dan npm. `package.json` tidak menetapkan versi Node.js atau npm tertentu. Gunakan rilis Node.js dan npm yang masih didukung serta cocok dengan `package-lock.json`. Lockfile memakai format versi 3 dan sesuai dengan `package.json`, jadi instalasi frontend menggunakan `npm ci`.

## Menyalin proyek

Ganti `<URL_REPOSITORI>` dengan URL Git proyek ini.

```bash
git clone <URL_REPOSITORI>
cd anufish
composer install
npm ci
```

Jangan gunakan `composer update` atau `npm update` untuk penyiapan awal karena keduanya dapat mengubah versi paket yang sudah dikunci.

## Menyiapkan environment

Salin contoh konfigurasi tanpa membaca atau membagikan isi `.env` milik orang lain.

macOS atau Linux:

```bash
cp .env.example .env
```

Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Buat kunci aplikasi setelah file `.env` tersedia:

```bash
php artisan key:generate
```

## Menyiapkan database

Konfigurasi bawaan `.env.example` memakai SQLite melalui `DB_CONNECTION=sqlite`. Sebelum migrasi pertama, buat berkas databasenya jika belum ada.

macOS atau Linux:

```bash
touch database/database.sqlite
```

Windows PowerShell:

```powershell
New-Item database/database.sqlite -ItemType File
```

Sebagai pilihan lain, `.env.example` menyediakan contoh MySQL dalam bentuk komentar. Buat database MySQL terlebih dahulu, lalu ganti bagian database di `.env`, misalnya:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=anufish
DB_USERNAME=root
DB_PASSWORD=
```

Jalankan migrasi setelah SQLite atau MySQL siap:

```bash
php artisan migrate
```

Migrasi membuat tabel untuk pengguna, cache, antrean pekerjaan, artikel, riwayat pemindaian, dan sesi. Konfigurasi contoh menyimpan sesi, antrean, dan cache di database, sehingga migrasi diperlukan sebelum aplikasi dipakai.

## Menjalankan aplikasi

Gunakan dua terminal dari direktori proyek.

Terminal 1, server Laravel:

```bash
php artisan serve
```

Terminal 2, Vite untuk aset frontend:

```bash
npm run dev
```

Buka `http://localhost:8000`. Saat menyiapkan aset untuk mode produksi, jalankan:

```bash
npm run build
```

## Struktur frontend

| Lokasi | Tanggung jawab |
| --- | --- |
| `resources/views/home.blade.php` | Halaman beranda |
| `resources/views/about.blade.php` | Halaman tentang |
| `resources/views/feature.blade.php` | Halaman fitur |
| `resources/views/profile/index.blade.php` | Halaman utama profil |
| `resources/views/components/layouts/` | Kerangka halaman bersama |
| `resources/views/components/navigation/` | Sidebar dan topbar |
| `resources/views/components/profile/` | Ringkasan dan formulir profil |
| `resources/views/components/ui/` | Komponen antarmuka umum seperti alert, card, button, dan input |
| `resources/css/app.css` | Impor Tailwind CSS dan tema warna Anufish |
| `resources/js/app.js` | Titik masuk JavaScript yang memuat dan menjalankan Alpine.js |

Panduan singkat saat mengubah tampilan:

- **Blade page** adalah satu halaman yang dibuka lewat rute, misalnya `home.blade.php`.
- **Blade component** adalah potongan tampilan yang dapat dipakai ulang, misalnya tombol atau topbar.
- **CSS dan Tailwind** mengatur tata letak, warna, jarak, dan gaya visual dari `resources/css/app.css` serta kelas di berkas Blade.
- **JavaScript dan Alpine.js** menangani interaksi ringan di browser. Alpine diimpor, dipasang pada `window`, lalu dijalankan dari `resources/js/app.js`.

Vite memproses dua titik masuk, yaitu `resources/css/app.css` dan `resources/js/app.js`.

## Mengambil pembaruan dari `main`

Perbarui salinan lokal sebelum membuat cabang kerja baru:

```bash
git switch main
git pull
```

Sesudah `git pull`:

- Jalankan `composer install` jika `composer.lock` berubah.
- Jalankan `npm ci` jika `package-lock.json` berubah.
- Jalankan `php artisan migrate` jika ada migrasi baru.

## Pemecahan masalah

| Masalah | Langkah pemeriksaan |
| --- | --- |
| Vite tidak berjalan | Buka terminal kedua dari direktori proyek lalu jalankan `npm run dev`. |
| CSS atau JavaScript tidak muncul | Pastikan `npm ci` selesai, jalankan `npm run dev`, lalu muat ulang halaman. Untuk aset hasil kompilasi, jalankan `npm run build`. |
| Dependensi PHP atau frontend hilang | Jalankan `composer install` untuk PHP dan `npm ci` untuk frontend. |
| Aplikasi melaporkan tabel belum ada atau migrasi tertunda | Pastikan database di `.env` sudah siap, lalu jalankan `php artisan migrate`. |

## Aturan kontribusi

Jangan mengerjakan perubahan langsung di `main`.

1. Perbarui `main` dengan alur di atas.
2. Buat cabang kerja, misalnya `git switch -c feat/nama-perubahan`.
3. Kerjakan dan periksa perubahan pada cabang tersebut.
4. Push cabang kerja ke repositori remote.
5. Buat Pull Request dan tunggu peninjauan sebelum perubahan digabungkan ke `main`.

Jaga perubahan tetap fokus. Jangan masukkan `.env`, kredensial, atau berkas lokal lain ke commit.

## Tim

- Nadya Nur Kamila
- Royyan Azka Ramadhana

## Lisensi

Proyek ini menggunakan lisensi [MIT](https://opensource.org/licenses/MIT), sesuai deklarasi pada `composer.json`.
