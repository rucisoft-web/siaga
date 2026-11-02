# Penyusutan & nilai buku
Daftar aset tetap pada nilai buku sesuai PSAK 16: harga perolehan, akumulasi penyusutan, nilai buku, beban penyusutan bulan berjalan dan tahun berjalan, serta usulan jurnal per kategori. Hanya untuk peran yang boleh melihat data keuangan. Tidak ada yang diinput di halaman ini.

![Penyusutan & nilai buku](img/penyusutan.png)
*Filter periode, kategori, dan lokasi; ringkasan; tabel per kategori; usulan jurnal.*

## Cara memakai
1. Pilih **Per akhir bulan** (default: bulan lalu), lalu bila perlu **Kategori** dan **Lokasi**.
2. Baca kotak angka: harga perolehan, akumulasi, nilai buku, beban bulan ini, dan beban sejak awal tahun.
3. **Ringkasan per kategori** dicocokkan dengan saldo buku besar aset tetap.
4. **Usulan jurnal penyusutan** dibukukan di sistem akuntansi (Debit beban penyusutan, Kredit akumulasi penyusutan).
5. **Ekspor Excel** berisi daftar aset tetap lengkap (satu baris per aset) dan sheet jurnal. Ekspor butuh izin ekspor laporan.

## Dari mana angkanya
- **Harga & tanggal perolehan**: tab **Keuangan** pada aset (atau impor master aset).
- **Metode, masa manfaat, nilai residu, nomor akun**: diatur per kategori di **Data referensi › Kategori aset**, bagian *Penyusutan*. Satu aset bisa menimpa nilai kategori di tab Keuangan.
- **Garis lurus**: (perolehan − residu) ÷ masa manfaat per bulan.
- **Saldo menurun ganda**: tarif 2 ÷ masa manfaat (tahun) dari nilai buku, sisa dihabiskan di bulan terakhir.
- Penyusutan dimulai bulan perolehan bila tanggal 1–15, dan bulan berikutnya bila setelah tanggal 15.
- Aset yang sudah dihapus (disposed) tidak ikut. Aset yang habis disusutkan tetap tampil dengan nilai buku sebesar residu.

## Tips
- Isi nomor akun per kategori agar jurnal siap diunggah ke ERP.
- Banyak aset "habis disusutkan tetapi masih dipakai" menandakan masa manfaat terlalu pendek atau aset sudah perlu diganti.
