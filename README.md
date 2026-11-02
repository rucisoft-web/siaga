# SIAGA — Sistem Informasi Aset & Gangguan

Aplikasi pengelolaan aset & pemeliharaan untuk **hotel mana saja** — **Laravel 13 · Filament 5 · MySQL 8**.
Satu paket, satu instalasi per hotel. Nama hotel, kode aset, logo, warna dan zona waktu diatur dari aplikasi
(**Pengaturan › Profil hotel**), tanpa mengubah kode.

Tampilan: tema **"Garis"** ala property management system — topbar gelap, garis aksen tipis, sudut siku,
tabel rapat berpembatas kolom, label kapital kecil, angka & kode monospace.

Instalasi: **PANDUAN-LARAGON.md** (komputer Windows) atau **PANDUAN-CPANEL.md** (hosting).

## Menyesuaikan untuk hotel baru

| Yang diatur | Di mana | Catatan |
|---|---|---|
| Nama hotel, kode properti, zona waktu, Super Admin | Ditanyakan saat instalasi (`php artisan siaga:install`) | Kode properti = prefiks kode aset, mis. `HMB` → `HMB-AST-000001` |
| Logo (terang & gelap), warna utama & aksen, nama aplikasi, tagline, alamat, kelas, jumlah kamar | Pengaturan › Profil hotel | 6 palet cepat; tanpa logo → logo teks bergaris otomatis |
| Gedung, lantai, kamar, area, tipe kamar | Pengaturan › Lokasi & kamar, Tipe kamar | |
| Departemen, kategori aset, vendor, SLA P1–P4, batas persetujuan | Menu Pengaturan | Data awal berisi 12 departemen & ~50 kategori aset hotel umum |
| Pengguna & peran | Pengaturan › Pengguna, Peran | 17 peran bawaan (termasuk Petugas Pendataan dan Tamu demo) (GM, Chief Engineering, Teknisi, FO, HOD, Finance, dll.) |

Kode properti terkunci setelah aset pertama dibuat, karena sudah tercetak di label.

## Modul

| Modul | Menu | Isi |
|---|---|---|
| Lokasi & referensi | Pengaturan, Lokasi & kamar | Gedung › lantai › kamar/ruang, tipe kamar, kategori, departemen, vendor, SLA P1–P4, batas persetujuan |
| Register aset | Aset › Register aset | Kode permanen `{KODE}-AST-000001`, kartu aset, verifikasi fisik, aktivasi, komponen, riwayat status, dokumen privat, kelompok jumlah |
| Impor master aset | Pengendalian Aset › Impor master aset | Workbook Excel (format GPCH) → tipe kamar, template isi kamar, ruang meeting, area umum + item estimasi, nomor kamar; uji coba dulu, aman diulang. CLI: `php artisan siaga:import-master file.xlsx` |
| Pendataan aset awal | Pengendalian Aset › Pendataan aset | Checklist per kamar untuk Mobile Terminal/HP: cetak label kamar langsung ke printer label Bluetooth, tempel & pindai (scanner bawaan/kamera), kondisi A–E, foto, item hitungan (linen, titik lampu), tidak ada / aset tambahan, selesai kamar; progres per lantai & per petugas |
| Stok label QR kosong | Pengendalian Aset › Stok label QR kosong | Label bernomor `{KODE}-QR-000001` tanpa nama aset, dihubungkan ke aset saat ditempel |
| Panduan implementasi | Lainnya › Panduan implementasi | Lingkup, jadwal 8 minggu, sumber daya, cek kualitas, struktur organisasi, komunikasi, risiko, pengadaan perangkat; bisa dicetak / PDF |
| Panduan per layar | Tombol **? Panduan** pojok kanan atas, menu Panduan | Penjelasan rinci dan tangkapan layar untuk setiap layar; bisa dicetak / disimpan PDF |
| Label QR | Pengendalian Aset | Token acak (hanya hash tersimpan), PDF ukuran aktual (A4 50×30, A4 75×50, roll 40×30/50×30/58×40/75×50) dan cetak langsung dari browser, cetak ulang beralasan, rotasi token, konfirmasi pemasangan, Scan QR (kamera / foto / ketik kode) |
| Laporan kerusakan | Pemeliharaan › Laporan kerusakan | Form cepat dari scan QR, saran prioritas, anti-tiket ganda, triase, gabung/tautkan |
| Work order | Pemeliharaan › Work order | Draft → persetujuan → tugas → dikerjakan → selesai → verifikasi → tutup; batas persetujuan, pembuat ≠ penyetuju, izin kerja, garansi, jam kerja, biaya, foto bukti |
| SLA & notifikasi | Lonceng notifikasi | Respons & pemulihan, kalender 24 jam / jam kerja, peringatan 80% & terlewat |
| Template & generate massal | Template aset kamar, Generate aset kamar | Template berversi, preview selisih, pencocokan aset lama, idempoten |
| Mutasi aset | Aset › Mutasi aset | Ajukan → setujui → kirim → terima → selesai |
| Blokir kamar | Pemeliharaan › Blokir kamar | Usulan Engineering, konfirmasi/lepas oleh Front Office |
| Laporan & KPI | Laporan & KPI | Backlog, kepatuhan SLA, MTTR, kegagalan berulang, downtime, biaya, ekspor CSV |
| Struktur lokasi | Aset › Struktur lokasi | Pohon gedung › lantai › kamar/ruang dengan jumlah aset, aset berisiko, WO terbuka, nilai, pencarian, daftar aset per lokasi |
| Dasbor pimpinan | Umum › Dasbor pimpinan | 13 grafik kondisi & risiko, kinerja perbaikan, biaya & dampak, filter lokasi, sorotan otomatis |
| Penyusutan & nilai buku | Laporan › Penyusutan & nilai buku | PSAK 16: garis lurus / saldo menurun ganda / tidak disusutkan, masa manfaat & residu per kategori (bisa ditimpa per aset), nilai buku per akhir bulan, usulan jurnal, ekspor Excel daftar aset tetap |
| Stock opname | Pengendalian Aset › Stock opname | Sesi per cakupan lokasi, hitung per ruang dengan scan QR, ditemukan / pindah lokasi / tidak ditemukan / temuan tak terdaftar, tutup & terapkan ke register, berita acara |
| Penghapusan aset | Pengendalian Aset › Penghapusan aset | Usul → setuju/tolak (pemisahan tugas) → selesai; jual, musnah, hibah, tukar tambah, hilang; nilai buku dibekukan, laba (rugi) pelepasan, usulan jurnal, berita acara |
| Keamanan | Pengaturan | 17 peran & 40 izin (deny by default), log audit, ganti sandi wajib, batas login, MFA |

Belum termasuk (rilis berikutnya, lihat rencana korporasi): konsolidasi grup lintas perusahaan, preventive maintenance terjadwal,
suku cadang & stok, anggaran, API & integrasi ERP, SSO, mode offline penuh, integrasi PMS. Indikator yang bergantung pada modul ini tampil **N/A**.

## Data contoh (terpisah, opsional)

Instalasi selalu **bersih** — siap diisi data asli hotel. Data contoh untuk latihan dipasang terpisah, kapan saja,
dengan `php artisan siaga:demo` (cPanel: `bash deploy/deploy-cpanel.sh demo`). Isinya: kamar 301–310 (Deluxe), template `TPL-DLX` (7 unit per kamar),
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

Domain email contoh diatur dengan `ASSETCARE_DEMO_EMAIL_DOMAIN`. Data contoh bercampur dengan data asli dan tidak dihapus otomatis, jadi untuk latihan sebaiknya pakai instalasi/database terpisah (mis. `siaga_latihan`).

## Situs demo & pra-workshop (landing page, masuk sebagai tamu)

Untuk calon peserta workshop 2 hari, SIAGA bisa dipasang sebagai situs demo.

**Hanya demo Grand Pasundan (disarankan untuk mulai):**

```bash
php artisan siaga:install --fresh                 # database KHUSUS demo; isi email & sandi Super Admin saat ditanya
php artisan siaga:demo-data gpch --admin          # hanya GPCH; Super Admin dipindah ke GPCH agar langsung melihat datanya
```

Perintah ini otomatis mengisi `.env` dengan `SIAGA_GUEST_MODE=true` dan `ASSETCARE_PROPERTY_CODE=GPCH` lalu memperbarui cache konfigurasi, sehingga landing page dan kotak **Coba SIAGA tanpa akun** (pilih perusahaan demo, lalu **Masuk sebagai tamu**) langsung tampil di atas form login. Tambahkan `--no-guest` bila tidak ingin mode tamu. Kalau `.env` tidak bisa ditulis, isi kedua baris itu manual lalu `php artisan config:cache`.
Hermina dan Kimia Farma tidak terpasang; bisa ditambah kapan saja dengan `siaga:demo-data hermina` / `siaga:demo-data kimia-farma`.

**Ketiga perusahaan demo:** `php artisan siaga:demo-data all` (cPanel: `bash deploy/deploy-cpanel.sh demo-site`), masing-masing 1–3 menit.

**Akun Super Admin (email & sandi):**
- Saat instalasi: dijawab di pertanyaan `siaga:install`, atau langsung `php artisan siaga:install --admin-email=admin@perusahaan.id --admin-password='SandiKuat#2026'`.
- Membuat admin baru / mereset sandi admin yang lupa: `php artisan siaga:create-admin --name="Nama Admin" --email=admin@perusahaan.id --password='SandiKuat#2026'`.
- Bila memakai impor `database/sql/siaga.sql` (tanpa terminal): masuk dengan `admin@siaga.local` / `Siaga@2026`, sistem langsung meminta ganti sandi. Email bisa diubah di menu **Pengguna**.
- Setelah masuk, sandi sendiri bisa diganti di menu **Akun saya**.

Hasilnya:
- **`/`**: landing page (Attention, Interest, Desire, Action): cek masalah, 7 masalah aset tetap beserta penyebab dan solusi SIAGA, cara kerja, grafik langsung dari data demo, tur tangkapan layar, manfaat per peran, agenda workshop, pilih data demo, lalu **Masuk sebagai tamu**.
- Halaman login `/admin/login` menampilkan kotak **Coba SIAGA tanpa akun** di atas form: pilih perusahaan demo, lalu **Masuk sebagai tamu**.
- **`/panduan`** dan **`/panduan/implementasi`**: panduan penggunaan per layar dan panduan implementasi, bisa dibaca tanpa login.
- Akun tamu bersama per perusahaan berperan **Tamu (demo)**: hanya melihat (termasuk nilai & biaya), tidak bisa mengubah data, profil, atau MFA.
- **Dasbor pimpinan** (menu Umum): 13 grafik kondisi & risiko aset, kinerja perbaikan, biaya & dampak, dengan filter per lokasi dan sorotan otomatis.

| Data demo | Isi (contoh / proyeksi) | Akun jabatan · sandi |
|---|---|---|
| Grand Pasundan Convention Hotel (GPCH) | 164 kamar, 2 gedung, ruang meeting, kitchen, laundry, power house · ±4.300 aset | `gm@gpch.demo`, `ce@gpch.demo`, … · `Gpch-2026!demo` |
| Hermina Group (HRM) | RS Hermina Kemayoran & RS Hermina Bekasi: 135 kamar rawat inap, IGD, ICU, NICU, bedah sentral, radiologi, hemodialisa · ±3.500 aset | `gm@hrm.demo`, … · `Hermina-2026!demo` |
| Kimia Farma (KF) | 47 outlet apotek / apotek + klinik / lab klinik di Bandung Raya + gudang distribusi · ±1.600 aset | `gm@kf.demo`, … · `KimiaFarma-2026!demo` |

Setiap data demo berisi pendataan berjalan, laporan kerusakan & work order di semua status, persetujuan biaya, blokir kamar/outlet,
mutasi, kalibrasi/PM, dan riwayat 6 bulan untuk tren. Fakta publik (alamat, kelas RS, jaringan outlet) diambil dari laporan tahunan 2025;
sisanya proyeksi dan ditandai "(demo)", "contoh", atau "asumsi". **Bukan data resmi perusahaan.**
Halaman depan bisa diaktifkan tanpa mode tamu dengan `SIAGA_LANDING=true`.

## Uji otomatis

`composer install` lalu `php artisan test` (database `asset_care_test`) — 29 skenario, termasuk penyusutan, stock opname & penghapusan aset, landing page & masuk sebagai tamu, dasbor pimpinan, pendataan, impor master, panduan, instalasi hotel baru,
Profil hotel (nama, warna, logo, zona waktu), kode properti terkunci, validasi warna, dan tidak ada sisa merek lama.

## Catatan teknis

- Waktu disimpan UTC, ditampilkan sesuai zona waktu di Profil hotel (WIB/WITA/WIT).
- Struktur sudah multi-properti (`property_id`); paket ini dipasang satu hotel per instalasi.
- Logo disimpan privat di `storage/app/private/brand` dan disajikan lewat `/brand/logo/...` (tidak perlu `storage:link`).
- URL label = `ASSETCARE_QR_BASE_URL/q/{token}`; tentukan domain final sebelum cetak massal.
- Pembaca QR memakai BarcodeDetector bawaan browser dengan cadangan jsQR (Apache-2.0, `public/js/vendor`).
