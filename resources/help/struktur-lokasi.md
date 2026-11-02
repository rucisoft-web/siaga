# Struktur lokasi
Pohon lokasi aset: gedung › lantai › kamar / ruang / area, lengkap dengan jumlah aset, aset berisiko, work order terbuka, dan nilai perolehan di setiap tingkat. Halaman ini hanya untuk dibaca; lokasi ditambah atau diubah di **Pengaturan › Lokasi** atau lewat **Impor master aset**.

![Struktur lokasi](img/struktur-lokasi.png)
*Kiri: pohon lokasi. Kanan: ringkasan lokasi yang dipilih dan daftar asetnya.*

## Cara memakai
1. Klik segitiga di depan gedung atau lantai untuk membuka / menutup cabangnya. **Buka semua** dan **Tutup semua** membuka atau menutup seluruh pohon.
2. Ketik di kotak **Cari lokasi** (mis. `203`, `Lantai 2`, `Kitchen`). Pohon hanya menampilkan lokasi yang cocok beserta induknya.
3. Klik **nama lokasi** untuk melihat ringkasannya di panel kanan: jumlah aset (termasuk sub-lokasi), yang langsung berada di lokasi itu, aset berisiko D/E, belum dinilai, WO terbuka, dan nilai perolehan.
4. Daftar **Aset di lokasi ini** mengurutkan aset berisiko lebih dulu. Klik aset untuk membuka kartu asetnya.
5. **Buka di Register aset** membuka register dengan filter lokasi tersebut (termasuk semua sub-lokasinya) untuk diekspor atau diolah lebih lanjut.

## Arti angka di setiap baris
- **aset**: jumlah aset yang belum dihapus (disposed) di lokasi itu dan seluruh sub-lokasinya.
- **berisiko**: aset dengan kondisi D (perlu kajian penggantian) atau E (tidak layak).
- **WO**: work order yang masih terbuka di lokasi tersebut.
- **Rp … jt**: jumlah harga perolehan; hanya tampil untuk peran yang boleh melihat data keuangan.

## Grup dengan beberapa badan usaha
Bila satu grup memiliki beberapa PT yang mengelola cabang di daerah berbeda, buat lokasi berjenis **Badan usaha (PT)** di tingkat teratas (Data referensi › Lokasi), lalu jadikan gedung / RS / wilayah sebagai sub-lokasinya. Pohon menjadi PT › gedung › lantai › kamar/ruang. Di matriks tersedia baris **Badan usaha (PT)**, dan filter lokasi di Penyusutan, Stock opname, serta Dasbor pimpinan bisa dipakai per PT.

## Tips
- Lokasi tanpa aset biasanya belum didata. Cek progresnya di **Pendataan aset › Progres**.
- Lantai atau gedung dengan banyak aset berisiko adalah kandidat program perbaikan / penggantian per area.

## Jenis barang: ada apa di sini, dan ada di mana
- Panel kanan menampilkan **Jenis barang di lokasi ini**, dikelompokkan per kategori, beserta jumlah unit dan aset berisiko D/E. "Nakas Unit 1" dan "Nakas Unit 2" dihitung sebagai satu jenis "Nakas".
- Klik salah satu jenis, atau isi **Jenis barang** / **Kategori** di atas pohon. Pohon kemudian hanya menampilkan lokasi yang memiliki barang tersebut, lengkap dengan jumlahnya, serta ringkasan "N unit di M lokasi" dan lokasi terbanyak.

## Matriks lokasi × jenis barang
Tab **Matriks** menampilkan tabel: baris = lokasi (pilih tingkat **Gedung**, **Lantai & area**, atau **Kamar / ruang**), kolom = kategori. Bila kategori dipilih, kolom berubah menjadi jenis barang. Warna sel makin gelap bila jumlahnya makin banyak.
- Baca **satu baris** untuk melihat isi sebuah lokasi.
- Baca **satu kolom** untuk melihat sebaran sebuah jenis barang. Klik judul kolom untuk merinci.
- Klik **sel** untuk membuka lokasi itu di pohon dengan filter yang sama.

![Matriks lokasi × jenis barang](img/struktur-lokasi-matriks.png)
