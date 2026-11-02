# Panduan Instalasi SIAGA di Laragon

Lokasi program: **`C:\laragon\www\siaga`** · Alamat: **http://siaga.test/admin**

Folder, database dan alamatnya terpisah dari aplikasi lain di Laragon (mis. Asset Care GPCH atau CRM), jadi bisa berjalan bersamaan.

## 1. Prasyarat

| Komponen | Ketentuan | Cara di Laragon |
|---|---|---|
| PHP | **8.3 atau lebih baru** (64-bit, NTS) | Menu Laragon › PHP › Version |
| Ekstensi PHP | `intl`, `pdo_mysql`, `mbstring`, `zip`, `gd`, `fileinfo`, `openssl` | Menu › PHP › Extensions. **gd** wajib untuk QR & PDF label. |
| MySQL | MySQL 8.x | Bawaan Laragon. Klik **Start All**. User `root` tanpa kata sandi. |
| Composer / Node.js | Tidak dibutuhkan | Folder `vendor` sudah disertakan; tampilan memakai CSS statis. |

## 2. Salin program

1. Ekstrak `siaga.zip` sehingga ada `C:\laragon\www\siaga\artisan`.
2. Klik **Reload** pada Laragon. Laragon membuat virtual host `siaga.test` ke folder `public`.

## 3. Instal untuk hotel Anda

Buka **Terminal** Laragon:

```bat
cd C:\laragon\www\siaga
setup-laragon.bat
```

Skrip menyiapkan `.env`, APP_KEY dan database `siaga`, lalu **menanyakan**:

| Pertanyaan | Contoh | Keterangan |
|---|---|---|
| Nama hotel | `Hotel Melati Bandung` | Bisa diubah lagi kapan saja |
| Kode properti | `HMB` | Prefiks kode aset (`HMB-AST-000001`). 2–8 huruf kapital/angka. **Terkunci setelah aset pertama dibuat.** |
| Zona waktu | WIB / WITA / WIT | |
| Email & sandi Super Admin | `it@hotelmelati.co.id` | Sandi minimal 10 karakter, huruf & angka |

Tanpa tanya jawab (mis. untuk beberapa hotel sekaligus):

```bat
php artisan siaga:install --hotel="Hotel Melati Bandung" --code=HMB --timezone=Asia/Jakarta --admin-email=it@hotelmelati.co.id --admin-password=SandiKuat2026 --fresh --no-interaction
```

> `setup-laragon.bat` memakai `--fresh`: menjalankannya ulang **menghapus semua data**.

Instalasi selalu **bersih** (tanpa data contoh), siap diisi data asli hotel.

### Data contoh (opsional, terpisah)

Untuk latihan atau demo, pasang data contoh kapan saja:

```bat
php artisan siaga:demo
```

Isinya kamar 301–310, template Deluxe, 10 aset contoh, dan akun per jabatan `@hotel.test` dengan sandi `Hotel-2026!ganti`. Data contoh bercampur dengan data asli dan tidak terhapus otomatis, jadi untuk latihan sebaiknya buat instalasi kedua (folder & database lain, mis. `siaga_latihan`).

## 4. Lengkapi Profil hotel

Login sebagai Super Admin → **Pengaturan › Profil hotel**:

- Unggah **logo** (PNG transparan ±400 × 120 px; versi gelap opsional). Tanpa logo, aplikasi memakai logo teks bergaris.
- Pilih **palet cepat** atau isi **warna utama** & **warna aksen garis** sendiri (mis. warna logo hotel).
- Lengkapi alamat, kota, kelas, jumlah kamar, telepon.

Lalu isi **Lokasi & kamar**, **Tipe kamar**, **Departemen**, **Pengguna**, dan sahkan **SLA** serta **Batas persetujuan** bersama manajemen.

## 5. Scan QR dari ponsel

- Ponsel dan komputer Laragon harus satu jaringan. Ganti `APP_URL` di `.env` menjadi alamat yang bisa dibuka ponsel (mis. `http://192.168.1.20`), lalu `php artisan optimize:clear`.
- **URL ini tercetak permanen di label.** Tentukan domain produksi (mis. `https://asset.namahotel.com`) di `ASSETCARE_QR_BASE_URL` **sebelum mencetak label massal**.
- Kamera di browser hanya aktif pada **HTTPS** (atau localhost). Pada HTTP gunakan tombol **Foto label** atau ketik kode aset.

## 6. Cetak label

**Pengendalian Aset › Cetak label QR** → pilih kamar/lantai/batch → media (A4 50×30 mm 3×9, A4 75×50 mm 2×5, atau roll) → **Buat PDF label**. Cetak pada **skala 100% / Actual size**. Nama hotel & warna utama ikut tercetak di label.

## 7. Peringatan SLA (scheduler)

- Uji coba: `php artisan schedule:work` (biarkan jendela terbuka).
- Permanen: Windows Task Scheduler, tiap 1 menit, program `C:\laragon\bin\php\php-8.3.x\php.exe`, argumen `C:\laragon\www\siaga\artisan schedule:run`.

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

## 8. Perintah penting

| Perintah | Kegunaan |
|---|---|
| `php artisan siaga:install` | Instalasi hotel (tanya jawab). Tambah `--fresh` untuk mengosongkan data. |
| `php artisan siaga:doctor` | Cek konfigurasi, database, izin & peran |
| `php artisan siaga:create-admin` | Membuat / memulihkan Super Admin |
| `php artisan siaga:import-master file.xlsx --dry-run` | Uji impor master aset (tanpa `--dry-run` untuk menyimpan, `--publish` untuk langsung menerbitkan template) |
| `php artisan test` | Uji otomatis (butuh `composer install` dan database `asset_care_test`) |
| `php artisan optimize:clear` | Bersihkan cache setelah mengubah `.env` |

## 9. Masalah umum

| Gejala | Solusi |
|---|---|
| Halaman putih / error 500 | Lihat `storage\logs\laravel-*.log`; jalankan `php artisan siaga:doctor`. |
| Tampilan berantakan | `php artisan filament:assets` lalu Ctrl+F5. |
| `could not find driver` | Aktifkan `pdo_mysql`. |
| PDF/QR gagal | Aktifkan ekstensi `gd`. |
| Logo / warna baru belum tampil | Muat ulang halaman (Ctrl+F5). |
| Kode properti tidak bisa diubah | Normal setelah ada aset. Untuk mulai dari nol: `php artisan siaga:install --fresh`. |
| Kamera tidak menyala | Normal di HTTP; gunakan **Foto label** atau pasang HTTPS. |

## Situs demo / pra-workshop (opsional)

Untuk calon peserta workshop: pasang di **database terpisah khusus demo**, lalu jalankan:

```
php artisan siaga:demo-data all
```

Perintah ini memasang tiga perusahaan demo terpisah (GPCH, Hermina Group dengan RS Kemayoran & Bekasi, Kimia Farma dengan jaringan apotek & klinik), masing-masing 1–3 menit. Setelah itu set `SIAGA_GUEST_MODE=true` di `.env` dan jalankan `php artisan config:cache`. Halaman depan (`/`) menampilkan landing page, pilihan data demo, dan tombol **Masuk sebagai tamu** (hanya melihat). Panduan bisa dibaca tanpa login di `/panduan`. **Jangan aktifkan mode tamu pada instalasi berisi data asli.**

**Hanya Grand Pasundan (GPCH):** `php artisan siaga:demo-data gpch --admin`. Hermina dan Kimia Farma tidak dipasang, dan akun Super Admin dipindah ke GPCH agar langsung melihat datanya. Lalu di `.env` isi `SIAGA_GUEST_MODE=true` dan `ASSETCARE_PROPERTY_CODE=GPCH`, kemudian `php artisan config:cache`.

**Email & sandi Super Admin:** ditentukan saat `siaga:install` (atau `--admin-email=... --admin-password=...`). Untuk membuat admin baru atau mereset sandi: `php artisan siaga:create-admin --name="Nama Admin" --email=admin@perusahaan.id --password='SandiKuat#2026'`. Impor `database/sql/siaga.sql` memakai `admin@siaga.local` / `Siaga@2026` dan langsung meminta ganti sandi.
