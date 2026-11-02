# Panduan Instalasi SIAGA di cPanel

Panduan ini memasang **SIAGA** (Laravel 13 + Filament 5 + MySQL 8) untuk **satu hotel** di hosting cPanel. Untuk hotel lain, ulangi dengan folder, subdomain dan database sendiri.

- Folder aplikasi: `/home/cpuser/siaga`, di **luar** `public_html`.
- Subdomain: `asset.namahotel.com`, dengan document root `siaga/public`.

Tampilan memakai tema **"Garis"** ala property management system. Nama hotel, logo dan warna diatur setelah instalasi di **Pengaturan › Profil hotel**.

> Ganti `cpuser` dengan nama pengguna cPanel Anda, dan `namahotel.com` dengan domain Anda.

---

## 0. Kebutuhan hosting

| Kebutuhan | Nilai |
|---|---|
| PHP | 8.3 atau 8.4 (MultiPHP) |
| Ekstensi PHP | pdo_mysql, mbstring, intl, gd, zip, fileinfo, openssl, xml, ctype, tokenizer |
| memory_limit | minimal 256M (untuk PDF label) |
| MySQL | 8.x (MariaDB 10.6+ umumnya juga jalan) |
| Fitur cPanel | Terminal (SSH), Cron Jobs, AutoSSL |
| Ruang disk | ±350 MB untuk aplikasi, ditambah lampiran foto |

Ekstensi diatur di **cPanel → Select PHP Version → Extensions**, atau di **MultiPHP INI Editor** untuk memory_limit.

---

## 1. Buat subdomain

1. Buka **cPanel → Domains → Create A New Domain**.
2. Isi Domain: `asset.namahotel.com`.
3. **Hapus centang** "Share document root", lalu isi Document Root: `siaga/public`.
4. Klik **Submit**.

Jika hosting tidak mengizinkan document root di luar `public_html`, lihat **Lampiran A**.

## 2. Atur versi PHP

Buka **cPanel → MultiPHP Manager**, centang `asset.namahotel.com`, lalu pilih **PHP 8.3** (atau 8.4) dan klik **Apply**.

## 3. Buat basis data

Buka **cPanel → Manage My Databases**, atau gunakan **Database Wizard**:

1. Buat database `siaga`. Nama lengkapnya menjadi `cpuser_siaga`.
2. Buat user `siaga` dengan sandi kuat. Nama lengkapnya menjadi `cpuser_siaga`. **Catat sandinya.**
3. Tambahkan user ke database dengan **ALL PRIVILEGES**.

## 4. Unggah aplikasi

1. Buka **cPanel → File Manager** dan masuk ke folder home (`/home/cpuser`), **bukan** ke `public_html`.
2. **Upload** `siaga.zip`.
3. Klik kanan zip tersebut, pilih **Extract**, dan ekstrak ke `/home/cpuser`.
4. Pastikan hasilnya berupa folder `/home/cpuser/siaga`, yang berisi `app`, `public`, `vendor`, `deploy`, dan seterusnya.
5. Hapus file zip setelah selesai diekstrak.

## 5. Siapkan file `.env`

Buka **cPanel → Terminal**, lalu jalankan:

```bash
cd ~/siaga
cp deploy/.env.cpanel.example .env
nano .env
```

Ubah minimal nilai-nilai berikut:

| Kunci | Isi |
|---|---|
| `APP_URL` | `https://asset.namahotel.com` |
| `ASSETCARE_QR_BASE_URL` | Sama dengan APP_URL. **Tercetak permanen di label**, lihat bagian 9. |
| `DB_DATABASE` | `cpuser_siaga` |
| `DB_USERNAME` | `cpuser_siaga` |
| `DB_PASSWORD` | Sandi dari langkah 3 |
| `MAIL_HOST` / `MAIL_USERNAME` / `MAIL_PASSWORD` | Akun email cPanel untuk notifikasi, misalnya `noreply@namahotel.com` di port 465 |
| `ASSETCARE_MFA_REQUIRED` | `true` (disarankan). Admin dan approver wajib memasang 2FA. |

Simpan dengan **Ctrl+O**, tekan **Enter**, lalu keluar dengan **Ctrl+X**. `APP_KEY` biarkan kosong karena akan diisi otomatis oleh skrip.

## 6. Instal aplikasi

Pilih **salah satu** cara berikut.

### Cara A (disarankan): skrip instalasi lewat Terminal

```bash
cd ~/siaga
bash deploy/deploy-cpanel.sh install     # basis data bersih: profil hotel + referensi + Super Admin
# opsional, kapan saja (sebaiknya di instalasi latihan terpisah):
bash deploy/deploy-cpanel.sh demo        # tambah data contoh: kamar 301–310, aset & akun contoh
```

Skrip ini melakukan langkah-langkah berikut:

1. Memeriksa PHP dan ekstensi.
2. Mengatur hak akses folder.
3. Membuat APP_KEY.
4. Menanyakan **nama hotel, kode properti, zona waktu, email & sandi Super Admin**, lalu membuat tabel dan data referensi (`siaga:install`).
5. Menjalankan `storage:link` dan mempublikasikan aset Filament.
6. Menyimpan cache konfigurasi.
7. Menjalankan pemeriksaan `siaga:doctor`.

Kode properti menjadi prefiks kode aset (`HMB` → `HMB-AST-000001`) dan **terkunci setelah aset pertama dibuat**. Skrip menolak berjalan dua kali pada basis data yang sudah berisi data, supaya data tidak tertimpa.

### Cara B: impor SQL lewat phpMyAdmin (bila Terminal tidak tersedia)

1. Buka **phpMyAdmin**, pilih `cpuser_siaga`, buka **Import**, lalu impor `database/sql/siaga.sql`.
2. Akun awal adalah `admin@siaga.local` dengan sandi `Siaga@2026`. Sandi ini wajib diganti saat login pertama.
   Profil awal bernama "Hotel Contoh" dengan kode `HTL`: ganti di **Pengaturan › Profil hotel** sebelum mendaftarkan aset pertama.
3. Langkah berikutnya tetap memerlukan Terminal sekali (atau minta bantuan penyedia hosting):
   ```bash
   cd ~/siaga && php artisan key:generate --force && bash deploy/deploy-cpanel.sh update
   ```

## 6b. Lengkapi Profil hotel

Login sebagai Super Admin, buka **Pengaturan › Profil hotel**: unggah logo, pilih palet warna (atau warna logo hotel), lengkapi alamat & jumlah kamar. Lalu isi Lokasi & kamar, Departemen, Pengguna, SLA dan Batas persetujuan.

## 7. Aktifkan HTTPS

1. Buka `https://asset.namahotel.com`. Jika ikon gembok muncul tanpa peringatan, SSL sudah terpasang otomatis. Lanjut ke langkah 2.
   Jika belum, pasang sertifikat gratis dengan salah satu cara berikut, sesuai menu yang tersedia di hosting Anda:
   - **cPanel → SSL/TLS Status**: centang subdomain, lalu klik **Run AutoSSL**.
   - **cPanel → Let's Encrypt SSL** (atau "Free SSL"): pilih subdomain, lalu klik **Issue/Install**.
   - Jika kedua menu itu tidak ada, minta CS hosting memasangkan SSL gratis untuk subdomain tersebut.

   Menu **SSL/TLS (Certificates)** hanya untuk mengunggah sertifikat manual, jadi tidak diperlukan.
2. Buka **cPanel → Domains**, lalu aktifkan **Force HTTPS Redirect** untuk `asset.namahotel.com`.

HTTPS **wajib** karena dua alasan:

- Kamera untuk scan QR hanya berfungsi di HTTPS.
- Cookie sesi diatur sebagai *secure*.

## 8. Pasang Cron Jobs

Buka **cPanel → Cron Jobs** dan tambahkan dua baris berikut. Sesuaikan path PHP jika versinya berbeda. Path yang benar ditampilkan di akhir skrip instalasi.

| Jadwal | Perintah | Fungsi |
|---|---|---|
| Setiap menit (`* * * * *`) | `/opt/cpanel/ea-php83/root/usr/bin/php /home/cpuser/siaga/artisan schedule:run >> /dev/null 2>&1` | Cek SLA, eskalasi, jadwal PM, dan notifikasi |
| Harian 02:00 (`0 2 * * *`) | `/bin/bash /home/cpuser/siaga/deploy/backup-db.sh >> /home/cpuser/backups/siaga-backup.log 2>&1` | Backup database harian dan arsip lampiran mingguan, disimpan 30 hari di `~/backups/siaga` |

## 9. Tentukan domain QR sebelum mencetak label

Setiap label QR berisi alamat `ASSETCARE_QR_BASE_URL/q/...`. **Setelah label dicetak dan ditempel, alamat ini tidak bisa diubah.** Karena itu:

- Tentukan domain final terlebih dahulu, misalnya `asset.namahotel.com`.
- Jika domain diganti, ubah `ASSETCARE_QR_BASE_URL` di `.env` lalu jalankan `php artisan optimize`, **sebelum** mencetak massal.
- Cetak 1 lembar uji, lalu scan dengan HP untuk memastikan alamatnya membuka halaman aset yang benar.

## 10. Verifikasi

| Cek | Hasil yang diharapkan |
|---|---|
| Buka `https://asset.namahotel.com` | Halaman login tema Garis dengan nama hotel Anda |
| Buka `https://asset.namahotel.com/.env` | **404**. Jika file .env terlihat, document root salah. Ulangi langkah 1. |
| `php artisan siaga:doctor` di Terminal | Semua baris berstatus **OK** |
| Login sebagai Super Admin | Diminta mengganti sandi, lalu memasang 2FA (Google Authenticator) |
| Aset → pilih aset → **Cetak label PDF** | PDF A4 3×9 berisi QR, kode, nama, dan ruang |
| Menu **Scan QR** di HP | Kamera aktif dan label terbaca |

---

## Pendataan aset awal (petugas lapangan)

Urutan kerja yang disarankan sebelum petugas keliling:

1. **Impor master aset** (Pengendalian Aset › Impor master aset): unggah workbook master aset, tekan **Uji coba**, periksa angka, lalu **Impor**. Tambahkan sheet **Daftar Kamar** (Nomor kamar · Jenis kamar · Gedung · Lantai) agar kamar ikut dibuat.
2. Periksa dan **terbitkan** template di **Template aset kamar**.
3. **Generate aset kamar** per lantai atau per tipe.
4. Buat akun petugas dengan peran **Petugas Pendataan**. Siapkan **Stok label QR kosong** untuk aset di luar template.
5. Petugas membuka **Pendataan aset** di Mobile Terminal/HP, lalu per kamar: cetak label → tempel → pindai → catat kondisi/foto → Selesai kamar.

**Mobile Terminal dan printer portable**
- Mobile Terminal Android dengan scanner bawaan: atur scanner ke mode *keyboard / keystroke output* dengan akhiran **Enter** (di aplikasi pengaturan scanner perangkat). Di layar Pendataan, kolom kode selalu siap menerima hasil scan tanpa memunculkan keyboard.
- Printer label Bluetooth: pasang aplikasi *print service* dari merek printer (mis. Zebra Printer Setup/Print Service, Brother Print Service Plugin), pasangkan printer lewat Bluetooth, lalu dari layar Pendataan ketuk **Buka & cetak**. Pilih printer, ukuran kertas sama dengan roll label, skala 100%.
- Aplikasi harus dibuka lewat **HTTPS** agar kamera HP bisa dipakai (pada Laragon tanpa HTTPS gunakan tombol **Foto label** atau scanner bawaan).

Panduan rinci tiap layar ada di tombol **? Panduan** pojok kanan atas aplikasi.

## Memperbarui ke rilis baru

1. Backup terlebih dahulu: `bash deploy/backup-db.sh`.
2. Unggah zip rilis baru, lalu ekstrak **menimpa** `~/siaga`. File `.env` dan `storage/` tidak ikut tertimpa karena tidak ada di dalam paket.
3. Jalankan:
   ```bash
   cd ~/siaga && bash deploy/deploy-cpanel.sh update
   ```
   Skrip ini akan memasang mode maintenance, menjalankan migrasi, memperbarui data referensi, membangun ulang cache, lalu menonaktifkan maintenance. Akun dan data tidak berubah.

**Situs demo yang sudah berjalan:** setelah `update`, jalankan juga `php artisan siaga:demo-data all --force`. Perusahaan yang sudah ada tidak digandakan; contoh stock opname, penghapusan aset, jadwal PM, dan badan usaha (Hermina) ditambahkan. Perusahaan yang belum ada dipasang.

## Memulihkan dari backup

```bash
gunzip -c ~/backups/siaga/cpuser_siaga_YYYY-MM-DD_HHMM.sql.gz | mysql -u cpuser_siaga -p cpuser_siaga
tar -xzf ~/backups/siaga/lampiran_YYYY-MM-DD.tar.gz -C ~/siaga/storage/app
cd ~/siaga && php artisan optimize:clear && php artisan optimize
```

> Simpan juga salinan file `.env` di tempat aman. **APP_KEY** dibutuhkan untuk membaca token label QR yang terenkripsi. Jika APP_KEY hilang, semua label harus dicetak ulang.

## Masalah umum

| Gejala | Penyebab dan solusi |
|---|---|
| Error 500 atau halaman putih | Lihat `storage/logs/laravel.log`. Biasanya disebabkan sandi DB salah atau `.env` belum lengkap. Setelah mengubah .env, jalankan `php artisan optimize`. |
| "Ekstensi ... tidak ada" saat menjalankan skrip | Aktifkan ekstensi di **Select PHP Version → Extensions** |
| Tampilan berantakan tanpa CSS | `APP_URL` tidak sama dengan alamat yang dibuka (http atau https). Perbaiki, lalu jalankan `php artisan optimize`. Jalankan juga `php artisan filament:assets`. |
| Foto atau lampiran tidak tampil | Jalankan `php artisan storage:link` |
| Login berulang kali kembali ke halaman login | Situs dibuka lewat http, padahal `SESSION_SECURE_COOKIE=true`. Aktifkan Force HTTPS. |
| Kamera scan tidak muncul | Situs belum HTTPS, atau izin kamera di browser ditolak |
| SLA atau notifikasi tidak berjalan | Cron `schedule:run` belum terpasang atau path PHP salah |
| PDF label gagal untuk banyak aset | Naikkan `memory_limit` ke 512M, atau cetak per ruang/kategori |
| Lupa sandi Super Admin | `php artisan siaga:create-admin` untuk membuat admin baru |

## Lampiran A: jika document root harus di dalam `public_html`

**Opsi 1: symlink** (jika diizinkan hosting)

```bash
rm -rf ~/public_html/asset        # hanya jika folder ini kosong/bawaan subdomain
ln -s ~/siaga/public ~/public_html/asset
```

Setelah itu, atur document root subdomain ke `public_html/asset`.

**Opsi 2: salin isi folder `public`**

1. Salin seluruh isi `~/siaga/public/` ke `~/public_html/asset/`.
2. Edit `~/public_html/asset/index.php` dan ubah path-nya:
   ```php
   require __DIR__.'/../../siaga/vendor/autoload.php';
   $app = require_once __DIR__.'/../../siaga/bootstrap/app.php';
   ```
   Jika baris `maintenance.php` ada, ubah dengan pola yang sama ke `/../../siaga/storage/framework/maintenance.php`.
3. Buat ulang tautan storage:
   ```bash
   ln -s ~/siaga/storage/app/public ~/public_html/asset/storage
   ```
4. Setiap kali ada pembaruan, salin ulang `public/css` dan `public/js`, lalu jalankan `php artisan filament:assets`.

## Catatan keamanan

- `APP_DEBUG=false` dan `APP_ENV=production`. Jangan ubah di server.
- `.env` sudah diatur dengan `chmod 600` oleh skrip. Jangan simpan file ini di `public_html`.
- `robots.txt` menolak semua mesin pencari.
- Admin dan approver wajib memakai 2FA (`ASSETCARE_MFA_REQUIRED=true`).
- Ganti sandi Super Admin awal. Buat akun per orang, dan jangan memakai akun bersama.
- Pantau `~/backups/siaga-backup.log` secara berkala, dan unduh backup ke luar server minimal sebulan sekali.

## Ringkasan nilai

| Item | Nilai |
|---|---|
| Folder aplikasi | `/home/cpuser/siaga` |
| Document root | `siaga/public` |
| URL | `https://asset.namahotel.com` |
| Database / user | `cpuser_siaga` / `cpuser_siaga` |
| Super Admin | Diisi saat instalasi (Cara A) atau `admin@siaga.local` / `Siaga@2026` (Cara B) |
| Cron | `schedule:run` setiap menit dan `backup-db.sh` setiap hari 02:00 |
| Backup | `~/backups/siaga` (30 hari) |

## Situs demo / pra-workshop (opsional)

Untuk calon peserta workshop: pasang di **database terpisah khusus demo**, lalu jalankan:

```
bash deploy/deploy-cpanel.sh demo-site
```

Perintah ini memasang tiga perusahaan demo terpisah, masing-masing 1–3 menit, dengan istilah sesuai jenis usahanya: GPCH (hotel: gedung › lantai › kamar), Hermina Group (rumah sakit: 2 PT contoh › RS Kemayoran & Bekasi › lantai › kamar), dan Kimia Farma (ritel: wilayah › rayon › outlet). Setiap sesi hanya melihat data satu perusahaan atau grup. Setelah itu set `SIAGA_GUEST_MODE=true` di `.env` dan jalankan `php artisan config:cache`. Halaman depan (`/`) menampilkan landing page, pilihan data demo, dan tombol **Masuk sebagai tamu** (hanya melihat). Panduan bisa dibaca tanpa login di `/panduan`. **Jangan aktifkan mode tamu pada instalasi berisi data asli.**

**Hanya Grand Pasundan (GPCH):** `bash deploy/deploy-cpanel.sh demo-gpch` (setara `php artisan siaga:demo-data gpch --admin`). Hermina dan Kimia Farma tidak dipasang, dan akun Super Admin dipindah ke GPCH agar langsung melihat datanya. Perintah ini otomatis mengisi `.env` (`SIAGA_GUEST_MODE=true`, `ASSETCARE_PROPERTY_CODE=GPCH`) dan memperbarui cache konfigurasi, sehingga kotak **Coba SIAGA tanpa akun** dengan pilihan perusahaan dan tombol **Masuk sebagai tamu** langsung tampil di atas form login. Data demo yang sudah terpasang cukup jalankan ulang perintahnya (data tidak digandakan).

**Email & sandi Super Admin:** ditentukan saat `siaga:install` (atau `--admin-email=... --admin-password=...`). Untuk membuat admin baru atau mereset sandi: `php artisan siaga:create-admin --name="Nama Admin" --email=admin@perusahaan.id --password='SandiKuat#2026'`. Impor `database/sql/siaga.sql` memakai `admin@siaga.local` / `Siaga@2026` dan langsung meminta ganti sandi.
