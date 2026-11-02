# Panduan Instalasi Hotel Asset Care di Laragon

Lokasi program: **`C:\laragon\www\hotel-asset-care`** · Alamat: **http://hotel-asset-care.test/admin**

Folder, database dan alamatnya terpisah dari aplikasi lain di Laragon (mis. Asset Care GPCH atau CRM), jadi bisa berjalan bersamaan.

## 1. Prasyarat

| Komponen | Ketentuan | Cara di Laragon |
|---|---|---|
| PHP | **8.3 atau lebih baru** (64-bit, NTS) | Menu Laragon › PHP › Version |
| Ekstensi PHP | `intl`, `pdo_mysql`, `mbstring`, `zip`, `gd`, `fileinfo`, `openssl` | Menu › PHP › Extensions. **gd** wajib untuk QR & PDF label. |
| MySQL | MySQL 8.x | Bawaan Laragon. Klik **Start All**. User `root` tanpa kata sandi. |
| Composer / Node.js | Tidak dibutuhkan | Folder `vendor` sudah disertakan; tampilan memakai CSS statis. |

## 2. Salin program

1. Ekstrak `hotel-asset-care.zip` sehingga ada `C:\laragon\www\hotel-asset-care\artisan`.
2. Klik **Reload** pada Laragon. Laragon membuat virtual host `hotel-asset-care.test` ke folder `public`.

## 3. Instal untuk hotel Anda

Buka **Terminal** Laragon:

```bat
cd C:\laragon\www\hotel-asset-care
setup-laragon.bat
```

Skrip menyiapkan `.env`, APP_KEY dan database `hotel_asset_care`, lalu **menanyakan**:

| Pertanyaan | Contoh | Keterangan |
|---|---|---|
| Nama hotel | `Hotel Melati Bandung` | Bisa diubah lagi kapan saja |
| Kode properti | `HMB` | Prefiks kode aset (`HMB-AST-000001`). 2–8 huruf kapital/angka. **Terkunci setelah aset pertama dibuat.** |
| Zona waktu | WIB / WITA / WIT | |
| Email & sandi Super Admin | `it@hotelmelati.co.id` | Sandi minimal 10 karakter, huruf & angka |
| Isi data contoh? | Ya untuk uji coba / pelatihan | Kamar 301–310, aset contoh, akun per jabatan |

Tanpa tanya jawab (mis. untuk beberapa hotel sekaligus):

```bat
php artisan assetcare:install --hotel="Hotel Melati Bandung" --code=HMB --timezone=Asia/Jakarta --admin-email=it@hotelmelati.co.id --admin-password=SandiKuat2026 --demo --fresh --no-interaction
```

> `setup-laragon.bat` memakai `--fresh`: menjalankannya ulang **menghapus semua data**.

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
- Permanen: Windows Task Scheduler, tiap 1 menit, program `C:\laragon\bin\php\php-8.3.x\php.exe`, argumen `C:\laragon\www\hotel-asset-care\artisan schedule:run`.

## 8. Perintah penting

| Perintah | Kegunaan |
|---|---|
| `php artisan assetcare:install` | Instalasi hotel (tanya jawab). Tambah `--fresh` untuk mengosongkan data. |
| `php artisan assetcare:doctor` | Cek konfigurasi, database, izin & peran |
| `php artisan assetcare:create-admin` | Membuat / memulihkan Super Admin |
| `php artisan test` | Uji otomatis (butuh `composer install` dan database `asset_care_test`) |
| `php artisan optimize:clear` | Bersihkan cache setelah mengubah `.env` |

## 9. Masalah umum

| Gejala | Solusi |
|---|---|
| Halaman putih / error 500 | Lihat `storage\logs\laravel-*.log`; jalankan `php artisan assetcare:doctor`. |
| Tampilan berantakan | `php artisan filament:assets` lalu Ctrl+F5. |
| `could not find driver` | Aktifkan `pdo_mysql`. |
| PDF/QR gagal | Aktifkan ekstensi `gd`. |
| Logo / warna baru belum tampil | Muat ulang halaman (Ctrl+F5). |
| Kode properti tidak bisa diubah | Normal setelah ada aset. Untuk mulai dari nol: `php artisan assetcare:install --fresh`. |
| Kamera tidak menyala | Normal di HTTP; gunakan **Foto label** atau pasang HTTPS. |
