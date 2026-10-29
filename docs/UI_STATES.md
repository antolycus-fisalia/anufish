# UI State Standard

FSH-34 mendefinisikan pola feedback UI bersama untuk Blade, Tailwind CSS,
dan Alpine.js.

## State

| State | Arti | Retry |
| --- | --- | --- |
| idle | Belum ada operasi aktif | Tidak |
| loading | Request/proses sedang berjalan | Tidak; submit dinonaktifkan |
| success | Server telah mengonfirmasi operasi berhasil | Tidak |
| validation | Input user tidak valid | Perbaiki field |
| empty | Request berhasil tetapi tidak ada data | Tidak |
| unknown | Proses berhasil tetapi hasil domain tidak dapat ditentukan | Sesuai konteks |
| partial | Data inti tersedia tetapi enrichment gagal/tidak tersedia | Boleh jika kegagalan sementara |
| failure | Operasi gagal dan hasil yang diminta tidak tersedia | Boleh jika aman |

## Mapping provider

- iNaturalist 404 tanpa data inti → `empty` atau `unknown` sesuai konteks.
- iNaturalist 404 pada enrichment dengan data inti tersedia → `partial`.
- HTTP 429, timeout, atau outage tanpa data inti → `failure`.
- HTTP 429, timeout, atau outage saat data inti sudah tersedia → `partial`.
- Foto tidak tersedia atau gagal dimuat → gunakan placeholder foto; jangan
  mengubah seluruh halaman menjadi `failure`.
- Hasil AI Ruvicode yang sudah tersedia tidak boleh dihapus ketika enrichment
  iNaturalist gagal.

## Interaction

- Submit ganda dicegah selama `loading`.
- Retry hanya tersedia untuk operasi yang aman diulang.
- Response lama tidak boleh menimpa response terbaru.
- `loading`, `success`, `unknown`, dan `partial` memakai live region sopan.
- `validation` dan `failure` memakai alert.
- Error field tetap menggunakan `<x-ui.validation-error>`.
- Saat validasi gagal, fokus diarahkan ke field invalid pertama bila alur
  JavaScript melakukan submit dinamis.

## Penggunaan

### Empty state

```blade
<x-ui.state
    state="empty"
    :title="__('Belum ada data')"
    :message="__('Data akan muncul di sini ketika sudah tersedia.')"
/>
