@echo off
setlocal
rem ================================================================
rem  SIAGA - instalasi otomatis di Laragon (Windows)
rem  Jalankan dari Terminal Laragon:  cd C:\laragon\www\siaga  lalu  setup-laragon.bat
rem  PERINGATAN: database siaga akan dikosongkan & diisi ulang.
rem ================================================================
cd /d "%~dp0"

echo [1/7] Memeriksa PHP...
php -r "exit(version_compare(PHP_VERSION,'8.3.0','>=')?0:1);"
if errorlevel 1 (
  echo PHP 8.3 atau lebih baru diperlukan. Laragon: Menu ^> PHP ^> Version.
  goto :gagal
)
php -r "foreach(['pdo_mysql','mbstring','intl','gd','zip','fileinfo','openssl'] as $e){if(!extension_loaded($e)){echo \"Ekstensi belum aktif: $e\n\";$x=1;}} exit($x??0);"
if errorlevel 1 (
  echo Aktifkan ekstensi di Laragon: Menu ^> PHP ^> Extensions, lalu ulangi.
  goto :gagal
)

echo [2/7] Menyiapkan file .env...
if not exist .env copy .env.example .env >nul

echo [3/7] Membuat APP_KEY...
php artisan key:generate --force
if errorlevel 1 goto :gagal

echo [4/7] Membuat database siaga (utf8mb4)...
mysql -u root -e "CREATE DATABASE IF NOT EXISTS siaga CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
if errorlevel 1 (
  echo Gagal membuat database. Pastikan MySQL Laragon sudah Start All.
  goto :gagal
)

echo [5/7] Data hotel: jawab beberapa pertanyaan (nama hotel, kode, zona waktu, Super Admin)...
php artisan siaga:install --fresh
if errorlevel 1 goto :gagal

echo [6/7] Menyiapkan aset tampilan Filament...
php artisan filament:assets
php artisan optimize:clear

echo [7/7] Pemeriksaan akhir...
php artisan siaga:doctor

echo.
echo ================================================================
echo  Selesai. Buka http://siaga.test/admin
echo  Login dengan email ^& kata sandi Super Admin yang baru diisi.
echo  Lengkapi logo ^& warna di menu Pengaturan ^> Profil hotel.
echo ================================================================
goto :eof

:gagal
echo.
echo Instalasi berhenti. Baca pesan di atas atau PANDUAN-LARAGON.md bagian Masalah umum.
exit /b 1
