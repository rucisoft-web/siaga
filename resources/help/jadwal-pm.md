# Jadwal PM, inspeksi & kalibrasi
Pemeliharaan terencana yang membuat work order sendiri. Setiap pagi (pukul 06.00) SIAGA memeriksa jadwal yang jatuh tempo, membuat work order **draft**, memberi tahu Chief Engineering dan PIC, lalu memajukan tanggal jatuh tempo berikutnya. Halaman ini juga memantau garansi aset dan kontrak vendor yang akan berakhir.

![Jadwal PM, inspeksi & kalibrasi](img/jadwal-pm.png)
*Kotak angka, daftar jadwal, dan formulir jadwal baru.*

## Membuat jadwal
1. Isi **Nama pekerjaan** dan pilih **Jenis**: *Preventive maintenance*, *Inspeksi*, atau *Kalibrasi*.
2. Pilih **Sasaran**:
   - **Satu aset**: ketik kode aset, mis. genset, lift, defibrillator.
   - **Kelompok**: kategori di lokasi tertentu (termasuk sub-lokasinya), mis. semua detektor asap di Lantai 2. Satu work order dibuat berisi daftar periksa per aset.
3. Pilih **Interval** (mingguan sampai tahunan), **Jatuh tempo berikutnya**, dan **Buat WO H-** (berapa hari sebelum jatuh tempo work order dibuat; kalibrasi biasanya H-14 agar sempat memesan vendor).
4. Isi bila perlu: PIC usulan, vendor, **Dasar kepatuhan** (mis. Permenkes, K3, HACCP), dan **Daftar periksa** satu langkah per baris.
5. Tekan **Buat jadwal**.

## Sehari-hari
- **Buat WO sekarang**: membuat work order untuk jatuh tempo terdekat tanpa menunggu pagi. Satu jatuh tempo hanya menghasilkan satu work order, jadi tombol ini aman ditekan dua kali.
- Work order PM diproses seperti work order lain: setujui, tugaskan teknisi, kerjakan, verifikasi, tutup.
- Tab **WO PM terbuka** menandai merah yang lewat jatuh tempo.
- **Kepatuhan PM 90 hari** = WO PM yang selesai sebelum jatuh tempo ÷ semua WO PM yang jatuh tempo dalam 90 hari terakhir.
- **Nonaktifkan** jadwal bila aset dihapus atau sedang tidak dipakai; riwayat tetap tersimpan.

## Garansi & kontrak
- Tanggal garansi diisi di tab **Perawatan** pada aset (*Garansi sampai*). Tanggal kontrak diisi di **Data referensi › Vendor** (*Kontrak berlaku s.d.*).
- Pengingat dikirim ke pengelola jadwal PM pada **H-30** dan **H-7**, masing-masing sekali.
- Tab **Garansi & kontrak** menampilkan yang berakhir dalam 60 hari atau sudah lewat ≤ 30 hari.

## Agar berjalan otomatis
Server harus menjalankan penjadwal Laravel tiap menit (cron `php artisan schedule:run`), sama seperti peringatan SLA. Untuk menjalankan manual: `php artisan siaga:pm-run`.
