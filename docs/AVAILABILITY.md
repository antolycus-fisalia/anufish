# Health check dan availability

| Endpoint | Kondisi | HTTP | JSON |
| --- | --- | --- | --- |
| `GET /up` | Aplikasi dapat merespons | 200 | `{"status":"up"}` |
| `GET /ready` | Database utama menerima query dan pemeliharaan sedang tidak aktif | 200 | `{"status":"ready"}` |
| `GET /ready` | Database atau pemeriksaan readiness gagal, atau pemeliharaan aktif | 503 | `{"status":"unavailable"}` |

Kedua endpoint bisa dipanggil tanpa login dan memakai `Cache-Control: no-store`. Gangguan iNaturalist dan Ruvicode dicatat terpisah. Untuk diagnosis readiness, periksa event `health.readiness_failed` di log aplikasi yang aksesnya dibatasi. Event tersebut hanya mencatat tipe exception.

## Catat satu sampel setiap 60 detik

Jalankan perintah berikut dari host pemantauan terpisah dengan PHP CLI dan ekstensi cURL. Gunakan URL deployment dan simpan file log di lokasi privat.

```bash
php scripts/check-availability.php https://anufish.example /var/log/anufish/availability.jsonl
```

Jadwalkan setiap menit, misalnya dengan cron `* * * * *`. Skrip menulis satu baris JSON dengan waktu UTC, hasil kedua endpoint, dan `available` bernilai `true` jika keduanya mengembalikan HTTP 200 dengan JSON sesuai kontrak. Kode keluar `0` berarti tersedia, `1` berarti pemeriksaan gagal, dan `2` berarti konfigurasi atau pencatatan gagal.

Contoh sampel saat aplikasi merespons tetapi database putus.

```json
{"timestamp":"2026-11-17T07:00:00Z","liveness":{"http_status":200,"ok":true},"readiness":{"http_status":503,"ok":false},"available":false}
```

## Laporan KNF-01

- Rekap per bulan kalender dalam zona waktu Asia/Jakarta. Catat waktu mulai dan akhir, jumlah menit pengamatan, menit pemeliharaan terjadwal, menit berhasil, menit gagal, menit tanpa sampel, dan insiden provider.
- Hitung **uptime = menit berhasil ÷ (total menit kalender − menit pemeliharaan terjadwal) × 100%**. Hitung maksimal satu hasil per menit. Jika satu menit punya beberapa sampel, gunakan hasil gagal bila ada. Menit tanpa sampel dilaporkan tersendiri dan tetap masuk penyebut.
- Laporkan pemeliharaan yang disetujui dan gangguan provider di luar angka downtime internal. Target berlangsung satu bulan kalender penuh dengan uptime minimal **99%**. Untuk handoff atau rilis sebelum bulan berakhir, beri label periode parsial.
