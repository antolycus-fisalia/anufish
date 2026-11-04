# FSH-22 — Keamanan artikel

## Dependensi dan batas scope

Branch feature/uc-07-article-security diselaraskan dengan main
043ea07da6153d6b90bf2f02704d2c72da99599d, termasuk PR #27 (CRUD artikel),
PR #28 (edit admin), dan riwayat scan.
Perubahan keamanan dipisahkan dari perbaikan FSH-21. Tidak ada merge otomatis ke
main atau perubahan UI/form/moderasi FSH-20/24/29 dalam pekerjaan ini.

## Policy

ArtikelPolicy didaftarkan eksplisit pada Gate. Akun harus berstatus aktif.

| Aksi | Pemilik | User lain | Admin bukan pemilik |
| --- | --- | --- | --- |
| create / viewAny | Ya | Ya, untuk artikel sendiri | Ya |
| view detail pengelolaan | Ya | Tidak | Ya |
| update / delete CRUD | Ya | Tidak | Tidak |
| moderate | Hanya jika admin | Tidak | Ya |

Admin tidak mendapat before() yang membuka semua aksi. Perubahan/hapus melalui
moderasi merupakan tanggung jawab endpoint FSH-29 dengan ability moderate,
bukan izin menulis ulang isi artikel milik orang lain melalui CRUD pengguna.
FormRequest memeriksa policy sebelum validasi/upload; show/delete memakai Gate.
Respons denial 403 dan pesan ID/EN. Owner/status selalu ditentukan server.

## Allowlist rich text

Gunakan Symfony HTML Sanitizer 7.4 (versi terkunci 7.4.22). HTML diproses sebagai
fragmen body, bukan regex. Elemen di luar allowlist beserta isinya dibuang.

- Tanpa atribut: p, br, strong, b, em, i, u, s, ul, ol, li, blockquote,
  h2, h3, pre, code.
- a: href dan title; rel dipaksa noopener noreferrer, target tidak diterima.
- img: src, alt dan title.
- Hanya URL absolut HTTP/HTTPS. URL relatif, protocol-relative, javascript,
  data, file, blob dan scheme lain ditolak. Event handler, style, id, class,
  SVG, MathML, script, iframe, form dan atribut lainnya tidak diterima.

Request asli dibatasi 50.000 karakter sebelum sanitasi; batas parser 200.000 byte
mencakup maksimum UTF-8. Konten wajib memiliki teks setelah sanitasi dan decode
entity, sehingga script-only, whitespace/NBSP/zero-width dan image-only ditolak
422 tanpa perubahan DB. Skema allowlist adalah keputusan pelaksanaan FSH-22,
bukan kutipan requirement SKPL baru.

Mutator isi menyanitasi sebelum penyimpanan model. Accessor juga menyanitasi
saat membaca/serialisasi agar payload legacy yang ditulis langsung lewat SQL
tidak keluar sebagai HTML berbahaya. Query builder/SQL bypass mutator; jangan
memakainya untuk menyimpan input artikel atau merender isi tanpa sanitasi.

## Handoff FSH-20/24

Form editor hanya menghasilkan format yang tercantum di allowlist. Judul/kategori
tetap dirender dengan escaping Blade biasa. Untuk body rich text gunakan:

```blade
<x-article.content :html="$artikel->isi" />
```

Komponen menyanitasi kembali sebelum output HTML, termasuk jika diberi payload
mentah. Jangan membuat raw echo pada halaman publik lain. FSH-24 tetap bertanggung
jawab untuk route/detail publik, filter status disetujui dan layout. Tes di task
ini membuktikan komponen output dan accessor legacy aman; bukan klaim halaman
publik FSH-24 sudah diimplementasikan.

## Upload dan lifecycle file

Gambar opsional: JPG/JPEG/PNG, maksimal 5 MB, ekstensi dan MIME yang diizinkan,
struktur serta decode JPEG/PNG valid. SVG, file palsu dan gambar rusak ditolak.
Batas 8 megapiksel mencegah decode gambar berukuran memori berlebihan; pesan
validasi lokal tersedia. Estimasi memori dicek sebelum decode; gambar ditolak
dengan pesan kurangi resolusi bila sisa memory_limit tidak mencukupi. GD digunakan untuk memvalidasi decode, tidak menerima
file hanya berdasarkan header MIME. Upload disimpan oleh Laravel Storage pada
disk public di artikel-images dengan nama hash hasil server, bukan nama klien.
Foto scan tidak dipindahkan ke public; ini hanya gambar artikel.

Save/update/delete DB dibungkus transaction. Bila DB gagal, file baru dibersihkan
dan data/file lama dipertahankan. File lama baru dibersihkan setelah transaksi
update berhasil; delete gambar setelah delete DB berhasil. Cleanup gagal dilaporkan
terpisah tanpa menutupi error awal atau mengubah sukses DB menjadi kegagalan palsu.
Tidak ada klaim transaksi atomik lintas filesystem/DB: kegagalan cleanup dapat
meninggalkan orphan file yang perlu ditindaklanjuti operasional. Respons gagal
hanya pesan generik ID/EN, tidak membocorkan exception/path sistem/kredensial.

## Dependency dan deployment

Jalankan composer install untuk lock baru, lalu migration artikel dari FSH-21 jika
belum ada. Tidak ada migration tambahan FSH-22. Aktifkan ekstensi GD dan DOM PHP.
Lock juga memperbarui symfony/mime ke patch 8.1.10 yang lolos advisory Composer,
beserta polyfill-mbstring yang terkait. Jangan menonaktifkan audit keamanan.

## Verifikasi

PHP memakai SQLite in-memory, fake storage dan payload sintetis; tanpa provider
live atau database pengguna. Tes mencakup policy lintas owner/admin, penolakan
sebelum upload, XSS/script/event/URL tersamarkan, format aman, payload legacy pada
komponen output, konten kosong, SVG/palsu/rusak, oversized file/dimensi, disk gagal,
rollback DB setelah write dan cleanup gambar lama/baru. Fixture UserFactory
menetapkan role/status sesuai default skema supaya model factory yang baru dibuat
mencerminkan akun aktif yang dibaca dari database.

Hasil pada tree final setelah sinkronisasi main, 4 November 2026:

- `php vendor/phpunit/phpunit/phpunit --colors=never`: 208 tes / 1115 assertions lulus;
  APP_KEY sintetis di environment proses, SQLite in-memory dari phpunit.xml.
- `php vendor/phpunit/phpunit/phpunit --filter ArticleSecurityTest`: 12 skenario keamanan.
- Pint `--test` pada file PHP yang diubah: lulus.
- `composer validate --no-check-publish`: valid.
- `composer audit --format=plain`: tidak ada advisory.
- `git diff --check`: lulus.

Tidak ada perubahan asset CSS/JS atau halaman visual publik; build tidak diperlukan
untuk perubahan backend ini. Komponen isi diuji lewat render Blade nyata dengan
payload berbahaya sintetis. FSH-24 tetap memerlukan QA halaman publik akhirnya.
Penerimaan FSH-22 tetap memerlukan review Royyan/Azka melalui PR; keberhasilan
tes bukan perubahan status otomatis menjadi Done.
