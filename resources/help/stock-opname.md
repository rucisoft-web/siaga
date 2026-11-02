# Stock opname
Pemeriksaan fisik aset secara berkala (disarankan minimal setahun sekali, atau per semester) untuk mencocokkan register dengan barang yang benar-benar ada. Hasilnya: aset ditemukan, ditemukan di lokasi lain, tidak ditemukan, dan temuan tak terdaftar, lengkap dengan berita acara.

![Hitung per ruang](img/stock-opname.png)
*Petugas memilih ruang, memindai label QR atau mengetuk Ada / Tidak ada.*

## Langkah
1. **Buka sesi** (Asset Controller / Finance): isi judul, cakupan lokasi (mis. satu gedung atau lantai; kosong = semua), dan tanggal cut-off. Daftar aset dalam cakupan disalin sebagai acuan; perubahan register sesudahnya tidak mengubah acuan.
2. **Hitung per ruang** (petugas): pilih ruang, lalu:
   - pindai label QR dengan kamera HP, Mobile Terminal, atau ketik kode aset. Aset yang terdaftar di ruang lain otomatis dicatat **ditemukan di lokasi lain**;
   - atau ketuk **Ada** / **Tidak ada** pada daftar;
   - bila kondisi berbeda, pilih **Kondisi yang dicatat saat pindai** sebelum memindai;
   - setelah ruang selesai, **Tandai sisa: tidak ditemukan**.
3. Barang yang ada tetapi tidak terdaftar dicatat di **Temuan aset tak terdaftar**, lalu didaftarkan di Register aset setelah opname.
4. **Tutup sesi**. Aset yang belum dihitung otomatis menjadi *tidak ditemukan*. Centang **Terapkan hasil ke register** untuk memperbarui lokasi aset yang pindah dan kondisi yang berubah, serta menandai aset terverifikasi. Semua perubahan tercatat di riwayat aset dengan nomor sesi.
5. Tab **Hasil & selisih** menampilkan daftar selisih. Untuk aset tidak ditemukan, klik **Usulkan penghapusan (hilang)** setelah dicari ulang.
6. **Berita acara** siap dicetak / disimpan PDF untuk ditandatangani petugas, kepala departemen, dan Finance.

![Hasil & selisih](img/stock-opname-hasil.png)

## Arti angka
- **Akurasi register** = ditemukan di tempat ÷ aset yang sudah dihitung. Target umum ≥ 98%.
- **Ditemukan di lokasi lain** menandakan mutasi yang tidak dicatat; tertibkan lewat menu **Mutasi aset**.

## Siapa yang boleh
- Mencatat hitungan: izin *Stock opname: mencatat hasil hitung fisik* (Asset Controller, Chief Engineering, Petugas Pendataan).
- Membuka, menutup, dan menerapkan hasil: izin *Stock opname: membuat, menutup & menerapkan hasil* (Asset Controller, Finance).
