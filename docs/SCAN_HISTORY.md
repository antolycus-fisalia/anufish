# FSH-17 — Riwayat scan

## Kontrak penyimpanan dan pembaca FSH-18

Server menyimpan satu riwayat setelah Ruvicode mengembalikan kandidat nonkosong.
Tidak ada endpoint yang menerima hasil AI, path file, confidence atau user_id dari
klien sebagai data riwayat. Kandidat yang belum dipilih tetap tersimpan dan diberi
label belum dipilih, bukan identifikasi spesies yang sudah terkonfirmasi.
Unknown/failure tidak membuat riwayat sukses dan foto sementaranya dibersihkan.

`POST /scan/upload`: multipart `fish_image` dan `operation_id` UUID. Frontend
membuat UUID per pemilihan foto dan mempertahankannya saat retry. Klien lama tanpa
UUID tetap diterima tetapi tidak mendapat jaminan replay lintas request.
Respons 201 mempertahankan struktur sebelumnya dan menambah `history_id` serta
`history_url`. Unknown menghasilkan kedua field tersebut dan path/disk NULL.
Kegagalan penyimpanan menghasilkan 500 dengan pesan ID/EN, bukan sukses.

Lock cache 180 detik per pengguna/operation ID mencegah request bersamaan memanggil
AI dua kali. Request yang masih berjalan menghasilkan 409 untuk dicoba kembali.
Replay sukses dengan UUID dan byte foto sama mengembalikan row lama tanpa AI,
foto baru atau insert baru. Byte berbeda dengan UUID yang sama menghasilkan 409.
Unique index `(user_id, operation_id)` menjadi penjaga terakhir di database.
Deployment beberapa instance harus menggunakan cache lock dan private storage
bersama; jangan memakai array cache untuk deployment tersebut.

`POST /scan/history/{history}/reference`: JSON `candidate_index`, CSRF token,
auth dan throttle 30/menit. Server mengambil kandidat dari row milik pengguna,
menjalankan ScanReferenceResolver, lalu menyimpan pilihan dan confidence asli
(nullable). Taxon ID hanya diisi dari hasil resolver dengan status matched:
exact scientific name, kelompok ikan, aktif, species, serta identitas diverifikasi
kembali lewat detail provider. Unavailable/ambiguous mengosongkan taxon ID.
Pemilihan takson ambigu di UI tidak otomatis dianggap match tervalidasi tersimpan.
Provider gagal tetap mempertahankan kandidat dan foto. Gagal menyimpan pilihan
menghasilkan 500 dan UI tetap mempertahankan hasil AI yang sudah tersimpan.

`GET /scan/history`: daftar terbaru milik login, paginasi 12 item.
`GET /scan/history/{history}`: detail kandidat, skor dan referensi tervalidasi.
`GET /scan/history/{history}/image`: foto privat dari local disk, no-store/nosniff.
Detail/foto/mutasi referensi milik pengguna lain menghasilkan 404; tamu ditolak
middleware auth. Tidak ada URL public storage untuk scan. Foto hilang mempunyai
fallback teks, bukan menghilangkan hasil AI. UI ID/EN, CSS/JS eksternal.

## Skema dan migrasi

Jalankan `php artisan migrate` setelah deploy kode sebelum menerima upload baru.
Tabel `riwayat_scan`: FK user_id; operation_id UUID; SHA-256 foto; private image
path; JSON kandidat asli; nullable selected index/nama ilmiah/confidence;
nullable indexed BIGINT inaturalist_taxon_id; match status; timestamps.
Tidak ada tabel master ikan atau FK untuk ID eksternal.

Baseline main tidak mempunyai tabel riwayat/provider lama. Migrasi ini hanya
membuat tabel baru: tidak membaca/menyalin ID GBIF maupun provider lain.
Jika suatu deployment mempunyai data lama di luar baseline ini, jangan menyalin
ID tersebut. Migrasi data harus terpisah, mempertahankan AI/foto/owner/timestamp,
menggunakan pencocokan ilmiah ulang tervalidasi atau membiarkan taxon ID NULL.
Rollback menjatuhkan tabel baru; lakukan backup sebelum rollback berisi data nyata.

## Verifikasi lokal

PHP menggunakan SQLite in-memory dan Storage::fake; provider memakai mock/fake.
Tidak ada panggilan Ruvicode berbayar atau klaim integrasi provider live.

- Suite PHP: 164 tes, 769 assertions lulus.
- `npm run test:scan`: 23 tes lulus, termasuk UUID retry dan POST pilihan server.
- Ditambah 2 tes UI state: total 25 tes JavaScript lulus.
- `npm run build`: lulus.
- Pint pada file PHP yang berubah dan `git diff --check`.

Skenario mencakup save owner asli, client owner/taxon/score diabaikan, replay tanpa
duplikasi file/row, konflik foto, lock inflight, UUID sama beda pengguna, unknown,
AI gagal, save=false, akses lintas owner, confidence NULL, referensi mismatch/
provider gagal, gambar hilang, serta render detail ID/EN.

QA visual browser desktop/mobile belum berhasil dilakukan pada sesi implementasi:
BrowserSkill tidak terpasang dan kontrol jendela Waterfox gagal mengaktifkan tab.
Render HTTP/Blade diuji otomatis; itu bukan pengganti verifikasi visual responsif.
Server QA localhost 8097 memakai database sintetis terpisah dan dihentikan setelah
percobaan pemeriksaan. Jangan menjalankan helper QA sebagai server aplikasi.
