# Impor master aset (Excel)
Memasukkan data awal dari workbook master aset: tipe kamar, template isi kamar per tipe, ruang meeting, area umum dengan item estimasi, dan nomor kamar. Ini langkah pertama sebelum petugas turun ke lapangan.

![Layar impor master aset](img/impor-master.png)
*Hasil uji coba workbook GPCH: 14 tipe kamar, 14 template, 14 ruang meeting, 383 item estimasi area umum.*

## Siapa yang memakai
Asset Controller atau Chief Engineering (hak **kelola template**).

## Format workbook
| Sheet | Kolom penting | Menjadi |
|---|---|---|
| Daftar Jenis Kamar | Jenis kamar, Alokasi gedung | Tipe kamar |
| Rujukan Aset Kamar | Kelompok, Aset, Satuan, lalu satu kolom per jenis kamar berisi jumlah per kamar | Template isi kamar |
| Nama Ruang Meeting | Nama ruangan, Perkiraan gedung | Ruang meeting (venue) |
| Master Aset Gabungan | Jenis Sumber, Gedung / Zona, Area / Ruangan, Kategori, Nama aset, Qty estimasi, Satuan | Area umum + item estimasi |
| Daftar Kamar (opsional) | Nomor kamar, Jenis kamar, Gedung, Lantai | Lantai & kamar |

Judul kolom dibaca dari baris pertama tiap sheet, jadi urutan kolom boleh berbeda.

## Langkah
1. Ketuk **Pilih file Excel**, pilih workbook `.xlsx`.
2. Ketuk **Uji coba (tidak menyimpan)**. Panel **Hasil** menunjukkan berapa data yang akan dibuat dan peringatan (mis. jenis kamar tidak dikenal, nomor kamar belum ada).
3. Bila angkanya sesuai, centang **Langsung terbitkan template kamar** bila template sudah diperiksa, lalu ketuk **Impor**.
4. Lanjutkan ke **Template aset kamar** untuk memeriksa template, lalu **Generate aset kamar**.

## Aturan pemetaan
- Satuan **titik, set, pcs, lot, par** dan kelompok **Linen** menjadi **item hitungan**: tidak diberi label QR per unit, cukup dihitung jumlahnya saat pendataan (mis. bantal, handuk, titik lampu).
- Aset lain (TV, AC, kulkas, kursi, dll.) menjadi aset per unit yang masing-masing mendapat label QR.
- Baris **Aset Kamar** di Master Aset Gabungan dilewati karena isinya sudah tercakup oleh template × jumlah kamar.
- Area umum dibedakan per gedung: "Lobby" di Main Building dan di West Wing menjadi dua ruang.
- Kode tipe kamar, gedung, dan lokasi dibuat otomatis dari nama.

## Catatan
- **Aman diulang**: data dengan nama yang sama dilewati. Lengkapi file (mis. tambah sheet Daftar Kamar), lalu impor lagi.
- Jumlah dari master adalah **estimasi**. Jumlah fisik dicatat petugas saat pendataan dan selisihnya terlihat di laporan.
- Baris contoh di sheet Daftar Kamar yang diawali `(contoh)` tidak diimpor.
