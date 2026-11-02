# Dasbor konsolidasi grup
Satu layar untuk direksi grup: membandingkan semua perusahaan dalam satu instalasi SIAGA (jumlah aset, nilai buku, aset berisiko, work order, SLA, biaya pemeliharaan, stock opname, penghapusan). Hanya untuk pengguna dengan peran **global** (super admin atau **Direksi grup**). Tidak ada yang diinput di halaman ini.

![Dasbor konsolidasi grup](img/dasbor-grup.png)
*Kotak angka gabungan, tabel perbandingan per perusahaan, dan grafik.*

## Cara memakai
1. Buka **Umum › Dasbor grup**. Angka diperbarui otomatis tiap 10 menit; tekan **Perbarui angka sekarang** bila perlu angka terbaru.
2. Baca kotak angka gabungan (jumlah semua perusahaan).
3. Pada **Perbandingan perusahaan**, warna menandai posisi terhadap target: hijau sesuai, kuning perlu perhatian, merah di bawah target.
4. Klik kode perusahaan untuk **berpindah** ke perusahaan itu dan membuka dasbor pimpinannya.
5. Untuk berpindah dari layar mana pun, klik kode perusahaan di pojok kiri atas (mis. **GPCH | WIB ▾**), lalu pilih perusahaan.

## Peran Direksi grup
- Diberikan oleh super admin di **Pengaturan › Pengguna**: centang peran **Direksi grup**. Peran ini berlaku untuk semua perusahaan dalam instalasi.
- Izinnya hanya melihat: dasbor, laporan, register aset dan nilai keuangan, work order, laporan kerusakan, dan log audit. Tidak bisa menyetujui biaya atau mengubah data.
- Setiap perpindahan perusahaan dicatat di **Log audit** (peristiwa *switch_property*).

## Tips
- Nilai buku memakai penyusutan per akhir bulan lalu (lihat **Laporan › Penyusutan**).
- Perusahaan dengan aset 0 biasanya belum selesai migrasi data; mulai dari **Impor master aset**.
