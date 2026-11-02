# Hotel Asset Care

Aplikasi pengelolaan aset & pemeliharaan untuk **hotel mana saja** — **Laravel 13 · Filament 5 · MySQL 8**.
Satu paket, satu instalasi per hotel. Nama hotel, kode aset, logo, warna dan zona waktu diatur dari aplikasi
(**Pengaturan › Profil hotel**), tanpa mengubah kode.

Tampilan: tema **"Garis"** ala property management system — topbar gelap, garis aksen tipis, sudut siku,
tabel rapat berpembatas kolom, label kapital kecil, angka & kode monospace.

Instalasi: **PANDUAN-LARAGON.md** (komputer Windows) atau **PANDUAN-CPANEL.md** (hosting).

## Menyesuaikan untuk hotel baru

| Yang diatur | Di mana | Catatan |
|---|---|---|
| Nama hotel, kode properti, zona waktu, Super Admin | Ditanyakan saat instalasi (`php artisan assetcare:install`) | Kode properti = prefiks kode aset, mis. `HMB` → `HMB-AST-000001` |
| Logo (terang & gelap), warna utama & aksen, nama aplikasi, tagline, alamat, kelas, jumlah kamar | Pengaturan › Profil hotel | 6 palet cepat; tanpa logo → logo teks bergaris otomatis |
| Gedung, lantai, kamar, area, tipe kamar | Pengaturan › Lokasi & kamar, Tipe kamar | |
| Departemen, kategori aset, vendor, SLA P1–P4, batas persetujuan | Menu Pengaturan | Data awal berisi 12 departemen & ~50 kategori aset hotel umum |
| Pengguna & peran | Pengaturan › Pengguna, Peran | 15 peran bawaan (GM, Chief Engineering, Teknisi, FO, HOD, Finance, dll.) |

Kode properti terkunci setelah aset pertama dibuat, karena sudah tercetak di label.

## Modul

| Modul | Menu | Isi |
|---|---|---|
| Lokasi & referensi | Pengaturan, Lokasi & kamar | Gedung › lantai › kamar/ruang, tipe kamar, kategori, departemen, vendor, SLA P1–P4, batas persetujuan |
| Register aset | Aset › Register aset | Kode permanen `{KODE}-AST-000001`, kartu aset, verifikasi fisik, aktivasi, komponen, riwayat status, dokumen privat, kelompok jumlah |
| Label QR | Pengendalian Aset | Token acak (hanya hash tersimpan), PDF ukuran aktual (A4 50×30, A4 75×50, roll), cetak ulang beralasan, rotasi token, konfirmasi pemasangan, Scan QR (kamera / foto / ketik kode) |
| Laporan kerusakan | Pemeliharaan › Laporan kerusakan | Form cepat dari scan QR, saran prioritas, anti-tiket ganda, triase, gabung/tautkan |
| Work order | Pemeliharaan › Work order | Draft → persetujuan → tugas → dikerjakan → selesai → verifikasi → tutup; batas persetujuan, pembuat ≠ penyetuju, izin kerja, garansi, jam kerja, biaya, foto bukti |
| SLA & notifikasi | Lonceng notifikasi | Respons & pemulihan, kalender 24 jam / jam kerja, peringatan 80% & terlewat |
| Template & generate massal | Template aset kamar, Generate aset kamar | Template berversi, preview selisih, pencocokan aset lama, idempoten |
| Mutasi aset | Aset › Mutasi aset | Ajukan → setujui → kirim → terima → selesai |
| Blokir kamar | Pemeliharaan › Blokir kamar | Usulan Engineering, konfirmasi/lepas oleh Front Office |
| Laporan & KPI | Laporan & KPI | Backlog, kepatuhan SLA, MTTR, kegagalan berulang, downtime, biaya, ekspor CSV |
| Keamanan | Pengaturan | 15 peran & 35 izin (deny by default), log audit, ganti sandi wajib, batas login, MFA |

Belum termasuk (rilis berikutnya): preventive maintenance terjadwal, suku cadang & stok, stock opname, disposal,
anggaran, API, mode offline penuh, integrasi PMS. Indikator yang bergantung pada modul ini tampil **N/A**.

## Data contoh (opsional)

Bila saat instalasi memilih **data contoh**: kamar 301–310 (Deluxe), template `TPL-DLX` (7 unit per kamar),
10 aset utilitas/kitchen/ballroom, dan akun per jabatan dengan sandi `Hotel-2026!ganti` (wajib diganti):

| Email | Peran |
|---|---|
| gm@hotel.test | General Manager |
| chief.engineer@hotel.test | Chief Engineering |
| eng.admin@hotel.test | Asset Controller |
| teknisi1@ / teknisi2@hotel.test | Teknisi |
| finance@hotel.test | Finance |
| fom@hotel.test | HOD + Front Office |
| fo.supervisor@ / hk.supervisor@hotel.test | Pelapor |
| owner@, director@, it@, exec.hk@, fbm@, purchasing@, auditor@ | Owner, perwakilan owner, IT, HOD, Purchasing, Auditor |

Domain email contoh diatur dengan `ASSETCARE_DEMO_EMAIL_DOMAIN`. Untuk hotel sungguhan, buat akun per orang dan nonaktifkan akun contoh.

## Uji otomatis

`composer install` lalu `php artisan test` (database `asset_care_test`) — 13 skenario, termasuk instalasi hotel baru,
Profil hotel (nama, warna, logo, zona waktu), kode properti terkunci, validasi warna, dan tidak ada sisa merek lama.

## Catatan teknis

- Waktu disimpan UTC, ditampilkan sesuai zona waktu di Profil hotel (WIB/WITA/WIT).
- Struktur sudah multi-properti (`property_id`); paket ini dipasang satu hotel per instalasi.
- Logo disimpan privat di `storage/app/private/brand` dan disajikan lewat `/brand/logo/...` (tidak perlu `storage:link`).
- URL label = `ASSETCARE_QR_BASE_URL/q/{token}`; tentukan domain final sebelum cetak massal.
- Pembaca QR memakai BarcodeDetector bawaan browser dengan cadangan jsQR (Apache-2.0, `public/js/vendor`).
