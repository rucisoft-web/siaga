# Pendataan aset
Layar kerja petugas pendataan di lapangan: per kamar/ruang, cetak label, tempel, pindai, catat kondisi, lalu selesaikan kamar. Dirancang untuk Mobile Terminal (Android dengan scanner bawaan) atau HP, ditambah printer label portable.

## Peralatan yang dibawa
- **Mobile Terminal / HP Android** dengan Chrome, login sebagai **Petugas Pendataan**.
- **Printer label portable** (Bluetooth) dengan roll label 50 × 30 mm (atau 40 × 30 / 58 × 40 mm). Pasang aplikasi *print service* dari merek printer (mis. Zebra, Brother, Xprinter) agar printer muncul di dialog cetak Android.
- Label QR stok kosong secukupnya untuk aset di luar template.
- Lap kering/alkohol untuk membersihkan permukaan sebelum menempel.

## 1. Pilih kamar
![Daftar kamar](img/pendataan-daftar.png)
*Daftar kamar: hijau = selesai, kuning = sedang dikerjakan, abu = belum.*

- Ketik nomor kamar atau nama ruang di kolom cari; saring per lantai/zona dan status (Belum selesai / Sudah selesai / Semua).
- Angka `3/7 item` = item yang sudah dicek dari total isi standar kamar.

## 2. Cetak label kamar ini
![Cetak label di kamar](img/pendataan-cetak.png)
*Setelah "Siapkan … label", tombol "Buka & cetak" membuka halaman cetak.*

1. Pilih ukuran label sesuai roll di printer (diingat selama login).
2. Ketuk **Siapkan N label belum dicetak**. Hanya label kamar ini yang belum pernah dicetak yang disiapkan.
3. Ketuk **Buka & cetak N label**. Halaman cetak terbuka dan dialog cetak Android muncul.
4. Pilih printer label, pastikan ukuran kertas sama dengan label dan skala 100%, lalu cetak.

![Halaman cetak label](img/label-cetak-langsung.png)
*Halaman cetak: satu label per halaman, urutan sama dengan daftar isi kamar.*

Label hilang atau rusak saat ditempel: buka item tersebut lalu ketuk **Cetak ulang label** (tercatat sebagai cetak ulang).

## 3. Tempel label, lalu pindai
![Isi kamar](img/pendataan-kamar.png)
*Bagian pindai dan daftar isi kamar.*

- **Mobile Terminal**: kolom kode selalu siap; tekan tombol scan di samping/gagang perangkat. Keyboard layar tidak muncul. Untuk mengetik kode manual, ketuk **Ketik**.
- **HP biasa**: ketuk **Mulai kamera** atau **Foto label**.
- Setelah dipindai, label tercatat **terpasang** di kamar ini dan item terbuka otomatis untuk dicatat kondisinya.
- Bila label milik kamar lain, muncul peringatan **label tertukar**. Lepas dan tempel label yang benar.

Letak label yang disarankan: sisi samping/belakang yang tidak terlihat tamu tetapi mudah dijangkau (belakang TV, sisi dalam pintu lemari, bawah meja, samping kulkas). Hindari permukaan panas, basah, atau kain.

## 4. Catat kondisi tiap item
![Catat kondisi](img/pendataan-item.png)
*Kondisi A–E, detail opsional, lalu Simpan · Ada.*

| Kondisi | Arti |
|---|---|
| A | Baik |
| B | Kerusakan ringan (kosmetik) |
| C | Perlu perbaikan |
| D | Perlu kajian penggantian |
| E | Tidak layak |

- Buka **Merek, model, serial, foto** untuk mengisi data nameplate dan memotret aset (disarankan untuk AC, TV, kulkas, brankas).
- **Simpan · Ada**: item tercatat ada, kondisi tersimpan, item berikutnya terbuka otomatis.
- **Tidak ada**: aset standar tidak ditemukan di kamar; ditandai selisih untuk ditindaklanjuti.
- Status item: **Belum**, **Label OK · cek kondisi**, **Kondisi OK · pindai label**, **Selesai**, **Tidak ada**.

## 5. Item hitungan
Linen, bantal, titik lampu, dan sejenisnya tidak diberi label per unit. Isi **jumlah fisik** di kolom angka, lalu **Simpan**. Jumlah standar ditampilkan sebagai pembanding.

## 6. Aset tambahan dan label stok kosong
![Aset tambahan](img/pendataan-tambahan.png)
*Aset di luar template dicatat langsung di kamar.*

- Aset yang ada di kamar tetapi tidak ada di daftar: ketuk **+ Aset tambahan**, isi kategori, nama, kondisi, foto, lalu simpan.
- Pakai **label stok kosong** (nomor `{KODE}-QR-…`): tempel, pindai, lalu ketuk **Pakai label … di sini** pada aset yang ditempeli, atau buat aset tambahan sehingga label langsung terhubung.

## 7. Selesai kamar ini
Ketuk **Selesai kamar ini** (tombol bawah). Sistem menolak bila masih ada item belum dicek atau item hitungan belum dihitung. Setelah selesai, ketuk **Kamar berikutnya** untuk lanjut.

Salah catat setelah selesai: buka kamar lalu **Buka lagi**.

## Progres (supervisor)
![Progres pendataan](img/pendataan-progres.png)
*Tab Progres: per lantai/zona dan per petugas.*

Supervisor, Chief Engineering, dan manajemen memantau jumlah kamar selesai, item ada/tidak ada, dan produktivitas petugas hari ini.

## Pembagian tim yang disarankan
- Satu tim = 1 petugas pencatat (Mobile Terminal + printer) dan 1 petugas tempel, atau 1 orang untuk kamar standar.
- Bagi per lantai agar tidak bertabrakan. Koordinasikan dengan Front Office: kerjakan kamar **vacant clean** lebih dulu.
- Target realistis kamar standar: 10–15 menit per kamar setelah terbiasa.
