# Scan QR
Membaca label QR aset untuk melihat kartu aset, melapor kerusakan, atau membuka pekerjaan terkait.

![Layar scan di HP](img/scan-qr.png)
*Layar Scan di HP. Kamera aktif bila aplikasi dibuka lewat HTTPS.*

## Cara memindai
1. Ketuk **Mulai kamera**, arahkan ke label QR sekitar 15 cm.
2. Bila kamera tidak bisa dipakai (jaringan lokal HTTP), ketuk **Foto label**, ambil foto label, lalu sistem membacanya.
3. Bila label rusak/tidak terbaca, ketik **kode aset** (mis. `{KODE}-AST-000123`) atau **nomor stok label** (mis. `{KODE}-QR-000045`) di kolom bawah lalu **Cari**.
4. Scanner USB atau tombol scan di Mobile Terminal juga bisa dipakai: hasil scan masuk ke kolom kode dan langsung dicari.

## Hasil pindai
| Hasil | Arti |
|---|---|
| Kartu aset | Label aktif dan terhubung ke aset. Dari sini bisa **Lapor kerusakan** atau melihat riwayat. |
| Label stok belum terhubung | Label QR kosong yang belum ditempel ke aset. Hubungkan lewat **Pendataan aset**. |
| Label tidak berlaku | Label sudah dirotasi/diganti. Laporkan ke Engineering agar diganti. |
| Tidak dikenal | QR bukan milik {APP} atau rusak. |

## Tips
- Hindari pantulan cahaya pada label laminasi; miringkan HP sedikit.
- Setiap pemindaian dicatat (waktu, pengguna, lokasi) untuk audit.
