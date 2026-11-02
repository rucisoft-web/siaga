# Asset Care GPCH

Aplikasi pengelolaan aset & pemeliharaan Grand Pasundan Convention Hotel — **Laravel 13 · Filament 5 · MySQL 8**, tampilan mengikuti Arah GPCH (warna hijau GPCH, ornamen wayang, font Inter & Cormorant Garamond).

Instalasi: lihat **PANDUAN-LARAGON.md** (cukup jalankan `setup-laragon.bat`).

## Isi Rilis A (sudah dibuat)

| Modul spesifikasi | Menu | Isi |
|---|---|---|
| M01 Lokasi & referensi | Pengaturan, Lokasi & kamar | Gedung › lantai › kamar/ruang, tipe kamar, kategori (~50), departemen, vendor, kebijakan SLA P1–P4, batas persetujuan |
| M02 Register aset | Aset › Register aset | Kode permanen `GPCH-AST-000001`, kartu aset, verifikasi fisik, aktivasi (field wajib), komponen, riwayat status, dokumen privat, kelompok jumlah |
| M03 Label QR | Pengendalian Aset | Token acak (hanya hash tersimpan), cetak massal PDF ukuran aktual (A4 50×30, A4 75×50, roll), mulai sel, cetak ulang beralasan, rotasi token, konfirmasi pemasangan per kamar, Scan QR (kamera / foto / ketik kode) |
| M04 Laporan kerusakan | Pemeliharaan › Laporan kerusakan | Form cepat dari scan QR, saran prioritas, anti-tiket ganda, triase dengan alasan, gabung/tautkan, tolak/duplikat |
| M05 Work order | Pemeliharaan › Work order | Status terkontrol (draft → persetujuan → tugas → dikerjakan → selesai → verifikasi → tutup, hold/batal/buka ulang), batas persetujuan, pembuat ≠ penyetuju, pekerjaan kritis diverifikasi orang lain, izin kerja, garansi, jam kerja, biaya sementara/final + reversal, foto bukti, cek versi (409) |
| SLA & notifikasi | Lonceng notifikasi | Respons & pemulihan, kalender 24 jam / jam kerja, hold tidak dihitung, peringatan 80% & terlewat (`assetcare:sla-check`) |
| Template & generate massal | Template aset kamar, Generate aset kamar | Template berversi (penyusun ≠ pemeriksa), preview selisih, pencocokan aset lama, kandidat draft, idempoten, konflik per kamar, retry |
| Mutasi aset | Aset › Mutasi aset | Ajukan → setujui → kirim → terima (kondisi) → selesai; slot kamar dilepas, label ditandai perlu cetak ulang |
| Blokir kamar | Pemeliharaan › Blokir kamar | Usulan Engineering, konfirmasi/lepas oleh Front Office (PMS tetap manual) |
| M08 Laporan | Laporan & KPI | Backlog & umur, kepatuhan SLA, MTTR bruto/bersih, kegagalan berulang, downtime, register, status label, biaya (khusus izin keuangan), ekspor CSV aman |
| M10 Keamanan | Pengaturan | 15 peran & 35 izin (deny by default), log audit dengan alasan & request id, ganti sandi wajib, batas login, MFA opsional, lampiran privat |

## Belum termasuk (Rilis B/C)

Preventive maintenance terjadwal, suku cadang & stok, stock opname/inventarisasi berkala, disposal, anggaran, API `/api/v1`, mode offline penuh (antrian kiriman), integrasi PMS. Indikator yang bergantung pada modul ini tampil **N/A** dengan keterangan.

## Akun contoh

Super Admin: `admin@assetcare.local` / `AssetCare@GPCH2026`. Akun jabatan memakai `GPCH-2026!ganti` (wajib diganti):

| Email | Peran |
|---|---|
| gm@grandpasundanhotel.com | General Manager |
| engineering@grandpasundanhotel.com | Chief Engineering |
| eng.admin@grandpasundanhotel.com | Asset Controller |
| teknisi1@ / teknisi2@grandpasundanhotel.com | Teknisi |
| ca@grandpasundanhotel.com | Finance |
| fom@grandpasundanhotel.com | HOD + Front Office |
| fo.supervisor@ / hk.supervisor@grandpasundanhotel.com | Pelapor |
| owner@, yodi@, edp@, hk@, fbm@, purchasing@, wiwin@ | Owner, perwakilan owner, IT, HOD, Purchasing, Auditor |

Nama, email & batas persetujuan adalah **contoh** — konfirmasi dengan HR/manajemen sebelum dipakai. Data contoh juga berisi kamar 301–310 (Deluxe) dan template `TPL-DLX` (7 unit per kamar) untuk mencoba generate massal.

## Uji otomatis

Paket ini berisi `vendor` tanpa alat uji. Untuk menjalankan uji: `composer install` lalu `php artisan test` (database `asset_care_test`) — 8 skenario: AC17 (10 kamar → 70 kandidat), AC18 (kirim ulang tanpa duplikat), AC19 (konflik per kamar), AC20 (pencocokan aset lama), alur laporan → WO lengkap dengan kontrol, PDF label + QR + pemasangan + rotasi, AC27 mutasi, akses ditolak secara default.

## Catatan teknis

- Waktu disimpan UTC, ditampilkan WIB (`ASSETCARE_DISPLAY_TIMEZONE`).
- Struktur sudah multi-properti (`property_id`), data awal satu hotel.
- URL label = `ASSETCARE_QR_BASE_URL/q/{token}`; tentukan domain final sebelum cetak massal.
- Pembaca QR memakai BarcodeDetector bawaan browser dengan cadangan jsQR (Apache-2.0, `public/js/vendor`).
