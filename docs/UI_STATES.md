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
```

### Loading

```blade
<x-ui.state
    state="loading"
    :title="__('Memuat data...')"
    :message="__('Mohon tunggu sementara data sedang dimuat.')"
/>
```

### Failure dan retry

```blade
<x-ui.state
    state="failure"
    :title="__('Data belum dapat dimuat')"
    :message="__('Silakan coba kembali.')"
>
    <button type="button" class="primary-action">
        {{ __('Coba lagi') }}
    </button>
</x-ui.state>
```

### Partial

Gunakan `partial` ketika data inti sudah tersedia tetapi enrichment atau
referensi tambahan gagal dimuat.

```blade
<x-ui.state
    state="partial"
    :title="__('Detail tambahan belum tersedia')"
    :message="__('Data utama tetap dapat digunakan.')"
>
    <button type="button" class="primary-action">
        {{ __('Coba lagi') }}
    </button>
</x-ui.state>
```

## Penggunaan pada Search

Search menggunakan pola berikut:

- `idle` ketika user belum melakukan pencarian.
- `loading` ketika pencarian sedang diproses.
- `empty` ketika request berhasil tetapi tidak menemukan data.
- `failure` ketika layanan pencarian gagal.
- hasil data tetap dirender normal ketika request berhasil.

Contoh empty:

```blade
<x-ui.state
    state="empty"
    :title="__('Ikan tidak ditemukan')"
    :message="__('Tidak ada hasil untuk pencarian ini.')"
/>
```

Contoh failure:

```blade
<x-ui.state
    state="failure"
    :title="__('Gagal mengambil data ikan')"
    :message="$error"
>
    <a href="{{ request()->fullUrl() }}" class="primary-action">
        {{ __('Coba lagi') }}
    </a>
</x-ui.state>
```

Search boleh tetap menggunakan loading skeleton sebagai varian visual untuk
state `loading`, selama semantics accessibility dan perilaku loading tetap
konsisten dengan standar ini.

## Penggunaan pada Scan

Scan menggunakan state berikut:

- `idle` sebelum proses identifikasi.
- `loading` selama AI memproses gambar.
- `unknown` ketika AI tidak menemukan kandidat ikan.
- `partial` ketika kandidat AI tersedia tetapi referensi iNaturalist gagal
  atau belum tersedia.
- `failure` ketika proses identifikasi gagal.
- `success` ketika referensi telah tervalidasi.
- `ambiguous` tetap menjadi state domain khusus Scan karena memerlukan
  pemilihan kandidat eksplisit.

Kegagalan iNaturalist tidak boleh menghapus kandidat dan confidence AI yang
sudah diterima dari Ruvicode.

Contoh partial:

```blade
<x-ui.state
    state="partial"
    :title="__('Detail referensi belum tersedia')"
>
    <x-slot:body>
        <span x-text="referenceError"></span>
    </x-slot:body>

    <button
        type="button"
        class="primary-action"
        @click="selectCandidate(selectedCandidateIndex)"
    >
        {{ __('Coba lagi') }}
    </button>
</x-ui.state>
```

## Validasi dan fokus

Field yang tidak valid harus memiliki:

```html
aria-invalid="true"
```

Komponen form proyek menggunakan atribut tersebut ketika server mengembalikan
validation error.

JavaScript mengarahkan fokus ke field invalid pertama:

```js
export function initInvalidFieldFocus() {
    const invalidField = document.querySelector(
        '[aria-invalid="true"]:not([disabled])'
    );

    if (!invalidField) {
        return;
    }

    invalidField.focus();
}
```

Tujuannya agar pengguna keyboard dan screen reader langsung mengetahui field
yang perlu diperbaiki.

## Contoh penggunaan pada Artikel

Contoh berikut hanya menunjukkan penggunaan shared UI state.
Bagian ini tidak mengimplementasikan fitur Artikel.

### Loading

```blade
<x-ui.state
    state="loading"
    :title="__('Memuat artikel...')"
    :message="__('Mohon tunggu sementara artikel sedang dimuat.')"
/>
```

### Empty

```blade
<x-ui.state
    state="empty"
    :title="__('Belum ada artikel')"
    :message="__('Artikel akan muncul di sini setelah tersedia.')"
/>
```

### Validation

Validasi field tetap menggunakan komponen yang sudah tersedia:

```blade
<x-ui.validation-error name="title" />
```

Field pertama dengan `aria-invalid="true"` menerima fokus agar error lebih mudah
ditemukan oleh pengguna keyboard dan screen reader.

### Success

State `success` hanya ditampilkan setelah server mengonfirmasi operasi berhasil.

```blade
<x-ui.state
    state="success"
    :title="__('Artikel berhasil disimpan')"
/>
```

### Failure

```blade
<x-ui.state
    state="failure"
    :title="__('Artikel belum dapat disimpan')"
    :message="__('Silakan coba kembali.')"
>
    <button type="button" class="primary-action">
        {{ __('Coba lagi') }}
    </button>
</x-ui.state>
```

## Accessibility

- `loading`, `success`, `unknown`, dan `partial` menggunakan `role="status"`
  dan `aria-live="polite"`.
- `validation` dan `failure` menggunakan `role="alert"` dan
  `aria-live="assertive"`.
- `loading` menggunakan `aria-busy="true"`.
- `idle` dan `empty` tidak memaksa live region.
- Live region tidak boleh ditumpuk tanpa kebutuhan.
- Tombol retry harus dapat digunakan melalui keyboard.
- Spinner bersifat dekoratif dan menggunakan `aria-hidden="true"`.
- Animasi spinner menghormati `prefers-reduced-motion`.
- Field invalid pertama menerima fokus ketika validation error ditampilkan.

## Aturan retry

Retry hanya ditampilkan jika operasi aman dijalankan kembali.

- Timeout provider → retry diperbolehkan.
- HTTP 429 → user diminta menunggu sebelum retry.
- Koneksi gagal → retry diperbolehkan.
- Foto tidak tersedia → gunakan placeholder, jangan retry seluruh halaman.
- Validation error → user memperbaiki field.
- Submit ganda → tombol dinonaktifkan selama `loading`.

Retry tidak boleh menghasilkan request berulang tanpa batas.

## Stale response dan abort

Response asynchronous lama tidak boleh mengganti state dari request yang lebih
baru atau state yang sudah di-reset.

Implementasi dapat menggunakan generation/token atau `AbortController`.

Contoh:

```js
const generation = ++this.requestGeneration;

const response = await fetch(url);

if (generation !== this.requestGeneration) {
    return;
}
```

Scan menggunakan generation untuk mencegah:

- decode gambar lama menimpa gambar terbaru;
- response referensi kandidat lama menimpa kandidat terbaru;
- response upload lama menghidupkan kembali hasil setelah scan di-reset.

## Prinsip data inti

Data inti yang sudah berhasil diperoleh tidak boleh disembunyikan hanya karena
enrichment tambahan gagal.

Contoh:

- Kandidat AI Ruvicode tetap ditampilkan ketika iNaturalist timeout.
- Nama dan takson inti tetap ditampilkan ketika foto gagal.
- Foto gagal dimuat menggunakan placeholder.
- Error enrichment ditampilkan sebagai `partial`, bukan mengubah seluruh
  halaman menjadi `failure`.

## Catatan verifikasi

- Fixture/mock digunakan untuk menguji timeout, HTTP 404, HTTP 429, outage,
  partial state, dan kegagalan yang sulit dipicu secara aman.
- Fixture/mock tidak dianggap sebagai bukti integrasi API live.
- Pengujian API live dicatat secara terpisah.
- Kegagalan enrichment tidak boleh menghapus data inti yang sudah tersedia.
- Hasil AI Ruvicode tetap dipertahankan ketika referensi iNaturalist gagal.

## Ringkasan kontrak

```text
idle
  ↓
loading
  ├─ success
  ├─ empty
  ├─ unknown
  ├─ partial
  ├─ validation
  ├─ failure
  └─ abort → idle / state sebelumnya
```

State domain khusus seperti `ambiguous` boleh digunakan oleh fitur tertentu,
tetapi tetap mengikuti pola accessibility, retry, dan stale response yang sama.
