# Register aset
Daftar seluruh aset per unit: kode, lokasi, kondisi, status operasional, siklus hidup, dan status verifikasi.

![Daftar aset](img/assets.png)
*Register aset dengan filter dan pencarian.*

## Membaca daftar
- **Kode aset** (`{KODE}-AST-000001`) dibuat otomatis dan tidak berubah seumur aset.
- **Siklus**: Draft (hasil generate/pendataan, belum diaktifkan), Aktif, Berhenti dipakai, Dihapus, Hilang.
- **Verifikasi**: Belum terverifikasi, Terverifikasi (dicek fisik), Selisih / tidak ditemukan.
- **Operasional**: Layak pakai, Menurun, Dalam perbaikan, Tidak beroperasi.
- Gunakan filter di kanan atas tabel (lokasi, kategori, status) dan kolom cari (kode, nama, serial).

## Kartu aset
![Kartu aset](img/assets-view.png)
*Kartu aset: identitas, lokasi, label, riwayat pekerjaan dan biaya.*

Tombol di kartu aset:
- **Lapor kerusakan**: membuat laporan untuk aset ini.
- **Cetak / cetak ulang label**: cetak ulang wajib menyertakan alasan.
- **Rotasi label**: membatalkan token QR lama (mis. diduga disalahgunakan) dan menerbitkan yang baru.
- **Verifikasi**: catat hasil cek fisik (ditemukan/tidak, kondisi).
- **Aktifkan**: mengubah draft menjadi aktif setelah data lengkap dan terverifikasi.
- **Ajukan mutasi**: memindahkan aset ke lokasi/departemen lain.
- **Pasang / lepas komponen**: untuk aset yang punya komponen (mis. kompresor pada chiller).

## Menambah aset manual
![Formulir aset](img/assets-create.png)
*Formulir aset baru.*

Isi nama, kategori, lokasi, departemen, merek/model/serial, kepemilikan, kritikalitas, dan data finansial bila ada. Untuk kamar, lebih cepat memakai **Generate aset kamar** dari template.

## Catatan
- Aset tidak dihapus dari database; gunakan status Berhenti dipakai, Dihapus, atau Hilang dengan alasan. Semua perubahan tercatat di Log audit.
