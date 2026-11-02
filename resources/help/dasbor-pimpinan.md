# Dasbor pimpinan
Grafik untuk pimpinan dalam satu halaman: kondisi dan risiko aset, kinerja perbaikan, serta biaya dan dampaknya. Halaman ini hanya untuk dibaca dan tidak ada yang perlu diisi. Semua angka dihitung langsung dari register aset, hasil pendataan, laporan kerusakan, work order, dan biaya yang tercatat.

![Dasbor pimpinan](img/dasbor-pimpinan.png)
*Bagian atas: filter lokasi, sorotan untuk pimpinan, dan grafik kondisi aset.*

## Cara memakai
1. **Pilih lokasi** (bila ada lebih dari satu gedung, rumah sakit, atau wilayah outlet). Pilih **Semua lokasi** untuk gabungan, atau satu lokasi untuk membandingkan.
2. Baca kotak **Sorotan untuk pimpinan**. Isinya kalimat otomatis tentang hal yang perlu diputuskan, misalnya aset berisiko, SLA di bawah target, aset yang biaya perbaikannya sudah mendekati harga baru, atau backlog yang terlalu lama.
3. Arahkan kursor (atau ketuk di HP) ke batang atau titik grafik untuk melihat angkanya.
4. Klik nama aset di tabel untuk membuka kartu aset dan riwayat perbaikannya.
5. Tombol **Cetak / PDF** menyiapkan halaman untuk rapat manajemen.

## 1 · Kondisi & risiko aset
- **Aset terdaftar / terverifikasi**: jumlah aset draft dan aktif, serta porsi yang sudah dicek fisik saat pendataan.
- **Nilai aset aktif**: jumlah harga perolehan yang tercatat (hanya tampil untuk peran yang boleh melihat data keuangan).
- **Aset berisiko**: aset dengan kondisi D/E atau berstatus tidak beroperasi / dalam perbaikan, beserta perkiraan nilai gantinya.
- **Kondisi per lokasi dan per kategori**: batang bertumpuk A–E. Kategori dengan porsi D/E besar adalah kandidat program penggantian.
- **Progres pendataan**: kamar/ruang selesai dan belum per lokasi, serta kurva kumulatif 30 hari.

## 2 · Kinerja perbaikan
- **Tren mingguan**: garis merah = work order masuk, batang = selesai. Bila garis terus di atas batang, backlog bertambah dan kapasitas teknisi perlu ditinjau.
- **Backlog per status & umur**: berapa pekerjaan menunggu persetujuan, dikerjakan, atau ditunda, dan sudah berapa lama.
- **SLA pemulihan per prioritas**: perbaikan yang pulih sebelum target waktu (hijau) dan yang terlambat (merah).
- **MTTR per kategori**: rata-rata jam dari laporan diterima sampai layanan pulih.

![Kinerja perbaikan dan biaya](img/dasbor-pimpinan-biaya.png)
*Bagian bawah: biaya per bulan, biaya per kategori, hari-kamar hilang, dan tabel aset untuk keputusan.*

## 3 · Biaya & dampak
- **Biaya per bulan**: material, jasa vendor, dan tenaga internal selama 6 bulan.
- **Biaya per kategori aset**: ke mana uang pemeliharaan paling banyak keluar.
- **Hari-kamar/ruang tidak terpakai**: total hari kamar, ruang, atau outlet yang diblokir karena perbaikan. Untuk hotel, ini setara kamar yang tidak terjual.
- **10 aset dengan biaya tertinggi**: kolom **Rasio** membandingkan biaya perbaikan 6 bulan dengan harga perolehan. Rasio 25% atau lebih (merah) menjadi sinyal untuk menimbang **perbaiki atau ganti**.
- **Kerusakan berulang**: aset yang rusak 2 kali atau lebih dalam 90 hari. Kaji akar masalahnya.
- **Aset berisiko yang perlu keputusan**: aset D/E atau tidak beroperasi, diurutkan dari yang kritis dan bernilai tinggi.

## Tips
- Angka biaya dan nilai aset hanya tampil untuk peran yang boleh melihat data keuangan (GM, Finance, Owner, Auditor).
- Di situs demo, seluruh angka adalah data contoh / proyeksi.
- Untuk tabel rinci yang bisa diunduh, buka **Laporan & KPI**.
