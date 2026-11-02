# Panduan implementasi pendataan aset
Rencana kerja pendataan aset awal dengan label QR sampai {APP} dipakai sehari-hari. Volume dihitung dari workbook master aset GPCH (164 kamar, 14 tipe kamar, 14 ruang meeting, 34 area umum). Angka di bawah adalah perkiraan; perbarui setelah pilot.

> **Ringkasan angka**
> - Kamar: **164**. Label aset per unit di kamar: **±4.300** (24–27 per kamar standar, 32–61 per suite). Item hitungan tanpa label (linen, bantal, titik lampu): **±820** kelompok.
> - Area umum, MEP, kitchen, meeting: **383** baris estimasi. Perkiraan label per unit: **±1.500–2.000** (tanpa kursi/meja banquet, sprinkler, detektor yang cukup dihitung).
> - Total label yang perlu disiapkan: **±6.500**, ditambah cadangan 15% sehingga **±7.500 label**.
> - Durasi: **8 minggu** dari persiapan sampai serah terima, dengan 2 tim lapangan.

## 1. Lingkup kerja

### Termasuk
- Penyusunan dan validasi **template isi kamar** untuk 14 tipe kamar (Standard, Deluxe A/B, Executive A/B, Suite).
- Pembuatan data lokasi: gedung (Main Building, West Wing, Sentral/Bersama), lantai, 164 kamar, 14 ruang meeting, area umum.
- **Pendataan fisik** seluruh kamar: tempel label QR, pindai, catat kondisi A–E, foto aset bernilai, merek/serial untuk aset teknik, hitung linen & item jumlah.
- **Pendataan area umum**: lobby, F&B outlet, kitchen, laundry, ruang meeting, power house, plant HVAC, pompa & reservoir, server room, kolam renang, area publik & parkir.
- Rekonsiliasi hasil lapangan dengan estimasi master aset; daftar selisih (tidak ada, tambahan).
- **Aktivasi aset** di register setelah lolos cek kualitas.
- Pelatihan pengguna: petugas pendataan, Engineering, FO, HK, Finance.
- Mulai pemakaian modul **laporan kerusakan** dan **work order** setelah aset aktif.

### Tidak termasuk (tahap berikutnya)
- Penilaian nilai buku / revaluasi aset dan penyelarasan dengan daftar aset tetap Accounting (data biaya bisa diisi menyusul).
- Inventaris barang habis pakai (amenities, F&B supplies) dan stok suku cadang.
- Preventive maintenance terjadwal, integrasi PMS.

### Prinsip pelabelan
| Jenis | Perlakuan | Contoh |
|---|---|---|
| Aset per unit bernilai/teknis | Label QR per unit | TV, AC indoor/outdoor, kulkas, brankas, kunci pintu elektronik, furnitur utama, genset, pompa, panel, oven, chiller |
| Item jumlah | Tanpa label per unit, dihitung | linen, bantal, handuk, titik lampu, gorden, kursi & meja banquet, sprinkler head, detektor area umum |
| Aset bergerak (pool banquet) | Label QR, lokasi = gudang banquet | proyektor mobile, sound portable, partisi |

## 2. Jadwal

| Minggu | Kegiatan | Hasil | Penanggung jawab |
|---|---|---|---|
| 0 | Keputusan manajemen, SK tim, anggaran, mulai pengadaan | SK tim, PO perangkat | GM, Purchasing |
| 1 | Instalasi {APP} di server (HTTPS, domain QR), profil hotel, akun pengguna; minta **daftar nomor kamar dari PMS** | Aplikasi siap, Daftar Kamar terisi | Admin IT |
| 1 | **Validasi template**: cek fisik 1 kamar contoh per tipe (14 kamar), koreksi jumlah & jenis di workbook | Template final, diterbitkan | Asset Controller, Chief Engineer |
| 2 | Impor master aset, generate aset kamar, uji cetak label & uji tempel (daya rekat, terbaca 2 jenis HP) | Aset draft & label siap | Asset Controller |
| 2 | Pelatihan petugas (½ hari teori, ½ hari praktik) + **pilot 1 lantai** | Waktu per kamar terukur, SOP disesuaikan | Koordinator lapangan |
| 3–4 | **Pendataan kamar** per lantai, 2 tim, ±20 kamar/hari/tim saat kamar tersedia | 164 kamar selesai | Tim pendataan |
| 5–6 | **Pendataan area umum**, MEP, kitchen, meeting & banquet (dijadwalkan di luar jam operasional/event) | 34 area & 14 ruang meeting selesai | Tim pendataan + PIC area |
| 7 | Cek kualitas sampel, rekonsiliasi selisih, perbaikan label, aktivasi aset | Register aktif, berita acara | QC, Asset Controller, Finance |
| 8 | Go-live laporan kerusakan & work order, serah terima, evaluasi | Berita acara serah terima | Chief Engineer, GM |

Catatan jadwal:
- Kamar hanya dikerjakan saat **vacant** (kosong). FO menyiapkan daftar kamar tersedia setiap pagi; pendataan bisa mundur bila okupansi tinggi.
- Perkiraan waktu: kamar standar 12–15 menit setelah terbiasa (pilot biasanya 25–30 menit), suite 30–60 menit, area besar (kitchen, power house) ½–1 hari.
- Sediakan 1 minggu cadangan untuk kamar yang terus terisi (long stay) dan area yang aksesnya terbatas.

## 3. Sumber daya yang perlu disiapkan

### Personel
| Peran | Jumlah | Asal | Waktu |
|---|---|---|---|
| Penanggung jawab proyek | 1 | Chief Engineer | 20% selama 8 minggu |
| Asset Controller / koordinator data | 1 | Engineering Admin atau Accounting | penuh waktu minggu 1–7 |
| Petugas pendataan | 4 (2 tim × 2 orang) | Engineering & Housekeeping (kenal kamar & peralatan) | penuh waktu minggu 3–6 |
| Pemeriksa kualitas (QC) | 1 | Accounting / Internal Audit (independen dari tim lapangan) | 30% minggu 3–7 |
| Admin IT | 1 | EDP/IT | 30% minggu 1–2, siaga setelahnya |
| PIC area | 1 per departemen | FO, HK, F&B/Banquet, Kitchen, Security | sesuai jadwal area |

Satu tim = **pencatat** (memegang handheld & printer, mencatat kondisi) + **penempel** (membersihkan permukaan, menempel, membuka akses, memotret). Kamar standar bisa dikerjakan 1 orang setelah terbiasa.

### Data
- Workbook master aset (sudah ada) dan **Daftar Kamar dari PMS**: nomor kamar, tipe, gedung, lantai.
- Daftar aset tetap dari Accounting (bila ada) untuk pencocokan kode lama.
- Denah per lantai untuk pembagian rute.

### Sistem & jaringan
- Server/hosting {APP} dengan **HTTPS** dan domain tetap untuk QR (mis. `https://aset.namahotel.com`). Domain **tidak boleh berubah** setelah label dicetak.
- **Wi-Fi staf** terjangkau di koridor kamar dan area belakang (cek titik lemah: basement, power house, kitchen). Sediakan paket data seluler pada handheld sebagai cadangan.
- Akun pengguna dengan peran **Petugas Pendataan** untuk tiap petugas.

## 4. Cek kualitas pendataan aset

### Pemeriksaan otomatis oleh aplikasi
- Kamar tidak bisa ditutup bila masih ada item belum dicek atau item hitungan belum dihitung.
- Label milik kamar lain ditolak (peringatan **label tertukar**).
- Satu label stok hanya untuk satu aset; satu aset hanya punya satu label aktif.
- Semua perubahan tercatat di Log audit (siapa, kapan, nilai lama/baru).

### Pemeriksaan harian (Asset Controller)
| Indikator | Target | Di mana dilihat |
|---|---|---|
| Kamar selesai vs rencana hari itu | ≥ 90% | Pendataan aset › Progres |
| Item **tidak ada** per kamar | ≤ 1 rata-rata; bila lebih, cek template atau kehilangan | Progres per lantai |
| Aset tambahan per kamar | ≤ 1; bila sering, template perlu direvisi | Progres per petugas |
| Label tercetak tapi belum terpasang | 0 di akhir hari | Laporan › Status label per lokasi |
| Foto & serial aset teknik (AC, TV, kulkas, brankas, mesin) | 100% | Register aset (filter kategori) |

### Uji petik independen (QC)
1. Setiap hari pilih acak **10% kamar** yang selesai kemarin (minimal 2 kamar per lantai).
2. QC datang tanpa petugas, pindai setiap label dengan HP biasa: label terbaca, nama & lokasi sesuai, kondisi wajar.
3. Hitung **akurasi** = item benar ÷ item diperiksa. Target **≥ 98%**. Di bawah 95%: lantai tersebut diulang dan petugas dilatih ulang.
4. Periksa kualitas fisik label: rata, tidak menggelembung, tidak di permukaan panas/basah, tidak terlihat tamu secara mencolok.

### Penerimaan per lantai/area
Lantai/area dinyatakan **diterima** bila: semua kamar selesai, uji petik lolos, selisih sudah diberi keterangan, dan Asset Controller serta Chief Engineer menandatangani berita acara. Setelah itu aset diaktifkan.

### Rekonsiliasi akhir
- Bandingkan jumlah fisik vs estimasi master per jenis aset (item hitungan menampilkan selisih otomatis).
- Aset **tidak ada**: telusuri (pindah ruang, diperbaiki, hilang) sebelum diputuskan; jangan langsung dihapus.
- Aset **tambahan**: pastikan bukan milik vendor/sewa; tandai kepemilikan.

## 5. Struktur organisasi pengelola aset

### Selama implementasi
```
Pengarah: General Manager (+ perwakilan pemilik)
 └─ Penanggung jawab proyek: Chief Engineer
     ├─ Koordinator data: Asset Controller
     │   ├─ Tim pendataan 1 (Engineering + HK)
     │   └─ Tim pendataan 2 (Engineering + HK)
     ├─ Pemeriksa kualitas: Accounting / Internal Audit (lapor juga ke GM)
     ├─ Admin IT
     └─ PIC area: FO, HK, F&B/Banquet, Kitchen, Security, Purchasing
```

### Setelah go-live (operasional)
| Fungsi | Pemegang | Tanggung jawab di {APP} |
|---|---|---|
| Pemilik proses aset | Chief Engineer | Menerbitkan template, menugaskan pekerjaan, verifikasi |
| Pengelola register | Asset Controller | Data aset, label, mutasi, inventarisasi berkala |
| Pengguna aset | Kepala departemen (HOD) | Menjaga aset di area, melapor kerusakan, menyetujui mutasi |
| Pelapor | Semua staf (FO, HK, F&B, Security) | Melapor kerusakan dengan scan QR |
| Pelaksana | Teknisi Engineering / vendor | Mengerjakan work order |
| Pengendali biaya | Finance | Batas persetujuan, finalisasi biaya, sinkron aset tetap |
| Pengawas | Internal Audit | Uji petik tahunan, review log audit |
| Administrator sistem | IT | Akun, peran, backup, keamanan |

### RACI ringkas
| Kegiatan | GM | Chief Eng. | Asset Ctrl | Tim lapangan | QC | FO/HK | IT |
|---|---|---|---|---|---|---|---|
| Template kamar | I | A | R | C | I | C | – |
| Jadwal akses kamar | I | A | R | I | – | R | – |
| Pendataan lapangan | I | A | R | R | I | C | C |
| Uji petik kualitas | I | I | C | C | R/A | – | – |
| Aktivasi aset | I | A | R | – | C | – | – |
| Akun & sistem | I | C | C | – | – | – | R/A |

R = mengerjakan, A = memutuskan, C = dimintai pendapat, I = diberi tahu.

## 6. Komunikasi

| Forum | Peserta | Waktu | Isi |
|---|---|---|---|
| Kick-off | GM, HOD, tim proyek | Minggu 0, 1 jam | Tujuan, jadwal, peran, apa yang diminta dari tiap departemen |
| Briefing pagi | Tim lapangan, Asset Controller, perwakilan FO | Setiap hari 08.30, 15 menit | Daftar kamar vacant hari ini, rute, kendala kemarin |
| Laporan harian | Asset Controller → Chief Engineer | Setiap sore | Progres (layar Progres), selisih menonjol, kebutuhan label |
| Rapat mingguan | GM, Chief Engineer, Finance, HOD terkait | Setiap Jumat, 30 menit | Progres vs jadwal, hasil QC, risiko, keputusan |
| Grup pesan proyek | Tim proyek & PIC area | Sepanjang proyek | Koordinasi cepat akses ruang; keputusan dicatat di rapat |

Aturan komunikasi:
- **FO** mengirim daftar kamar vacant clean setiap pagi dan memberi tahu bila kamar mendadak dijual.
- **HK** diberi tahu kamar yang sudah didata agar kondisi dirapikan kembali; petugas tidak mengubah tata letak kamar.
- **Banquet/F&B** memberi jadwal ruang meeting kosong; pendataan tidak dilakukan saat ada event.
- Petugas memakai seragam & ID, tidak masuk kamar terisi tamu. Bila tamu bertanya: "Pemeriksaan perlengkapan kamar oleh Engineering".
- Eskalasi: petugas → Asset Controller (hari yang sama) → Chief Engineer (≤ 1 hari) → GM (masalah lintas departemen).
- Pengumuman ke seluruh staf sebelum go-live: cara melapor kerusakan dengan scan QR.

## 7. Daftar risiko dan penanggulangan

| No | Risiko | Dampak | Penanggulangan |
|---|---|---|---|
| 1 | Okupansi tinggi, kamar tidak tersedia | Jadwal mundur | Koordinasi harian dengan FO; prioritaskan kamar vacant; manfaatkan hari check-out tinggi; cadangan 1 minggu |
| 2 | Template tidak sesuai kondisi nyata | Banyak "tidak ada"/"tambahan" | Validasi 1 kamar per tipe sebelum generate; revisi template bila selisih berulang di ≥ 3 kamar |
| 3 | Label pudar/terkelupas | QR tidak terbaca | Label sintetis (PP/PET) berperekat permanen; uji tempel di pilot; hindari permukaan panas, lembap, berminyak; bersihkan dengan alkohol sebelum tempel |
| 4 | Label direct thermal pudar terkena panas/UV | Label perlu diganti 1–2 tahun | Pakai label direct thermal **top-coated sintetis**; untuk aset luar ruang/panas cetak thermal transfer resin di kantor; pantau saat inventarisasi tahunan |
| 5 | Label tertukar antar kamar | Data lokasi salah | Cetak per kamar di kamar itu; aplikasi menolak label kamar lain; uji petik QC |
| 6 | Wi-Fi lemah di area tertentu | Pindai gagal | Survei sinyal di minggu 1; paket data seluler di handheld; kerjakan area tersebut belakangan |
| 7 | Perangkat rusak/baterai habis | Tim berhenti | 1 handheld & 1 printer cadangan; power bank; isi daya penuh setiap malam |
| 8 | Petugas kurang teliti/terburu-buru | Data tidak akurat | Pelatihan & pilot; target realistis; uji petik ≥ 98%; umpan balik harian |
| 9 | Tamu terganggu | Keluhan | Hanya kamar vacant; jam kerja di luar jam check-in sibuk; seragam & ID |
| 10 | Domain QR berubah setelah label dicetak | Semua label tidak bisa dipakai | Tetapkan domain final sebelum cetak; jangan pakai IP/alamat sementara |
| 11 | Data ganda dengan daftar aset Accounting | Selisih nilai buku | Isi "kode lama" saat pendataan; rekonsiliasi dengan Finance di minggu 7 |
| 12 | Keamanan akun | Data diubah pihak tidak berwenang | Akun per orang, peran sesuai tugas, MFA untuk admin, akun petugas dinonaktifkan setelah proyek |
| 13 | Aset vendor/sewa ikut didata sebagai milik hotel | Register keliru | Tandai kepemilikan (milik/sewa/vendor/konsinyasi) saat pendataan |
| 14 | Pengadaan terlambat | Pendataan mundur | Pesan di minggu 0; pilot bisa memakai HP Android + label A4 dari printer kantor |

## 8. Pengadaan yang diperlukan

Kebutuhan untuk **2 tim lapangan + 1 cadangan**. Harga adalah kisaran pasar Indonesia sebagai gambaran anggaran; minta minimal 2 penawaran.

| No | Barang | Spesifikasi minimum | Jml | Perkiraan harga satuan |
|---|---|---|---|---|
| 1 | **Handheld / Mobile Terminal Android** | Android 11+, scanner **2D** bawaan (QR) dengan mode *keyboard wedge* + Enter, layar ≥ 5", Wi-Fi 5 GHz, 4G, kamera ≥ 13 MP, baterai ≥ 4.000 mAh bisa diganti, tahan jatuh 1,2 m, IP54+ | 3 | Rp 4–12 juta |
| 2 | **Printer label portable** | Bluetooth (dan/atau Wi-Fi), lebar media 2 inci (≥ 50 mm), 203 dpi, mendukung label gap/die-cut, tersedia **aplikasi print service Android**, baterai seharian, klip sabuk | 3 | Rp 3–8 juta |
| 3 | **Label QR untuk printer portable** | 50 × 30 mm die-cut, **sintetis (PP/PET) direct thermal top-coated**, perekat permanen, inti sesuai printer | ±8 roll × 1.000 | Rp 200–450 ribu/roll |
| 4 | Printer label desktop (opsional, disarankan) | Thermal transfer, 300 dpi, untuk cetak massal per kamar di kantor dan label tahan lama | 1 | Rp 6–12 juta |
| 5 | Label polyester + ribbon resin (untuk printer desktop) | 50 × 30 mm polyester putih matte, ribbon resin | 3–4 roll + ribbon | Rp 300–600 ribu/roll; ribbon Rp 300–600 ribu |
| 6 | Label A4 (cadangan/pilot) | A4 3 × 9 (50 × 30 mm) atau 2 × 5 (75 × 50 mm), bahan vinyl/PP untuk printer laser | 20 pak | Rp 80–200 ribu/pak |
| 7 | Power bank | 20.000 mAh, PD fast charging | 3 | Rp 250–500 ribu |
| 8 | Charger & kabel cadangan, docking/charging cradle | sesuai perangkat | 1 set | Rp 0,5–1 juta |
| 9 | Holster/sarung & tali leher handheld | | 3 | Rp 100–250 ribu |
| 10 | Tas kerja lapangan | memuat printer, label, perlengkapan | 2 | Rp 150–300 ribu |
| 11 | Perlengkapan tempel | tisu alkohol (isopropil), lap mikrofiber, sarung tangan, senter kecil, cutter | 1 paket/tim | Rp 200–400 ribu |
| 12 | Amplop/ziplock bernomor kamar | untuk label cetak massal per kamar | 200 | Rp 100–200 ribu |
| 13 | Paket data seluler | untuk handheld selama proyek | 3 × 2 bulan | Rp 100 ribu/bulan |
| 14 | Hosting/server & SSL | PHP 8.3, MySQL/MariaDB, HTTPS, domain tetap | 1 | sesuai paket hosting |
| 15 | Tablet/laptop supervisor (bila belum ada) | untuk memantau progres dan QC | 1 | Rp 3–8 juta |

Catatan pemilihan perangkat:
- **Uji sebelum membeli dalam jumlah banyak**: pinjam 1 unit demo, coba pindai label di kamar dan cetak dari layar Pendataan.
- Pastikan scanner handheld bisa diatur mengirim hasil scan sebagai ketikan diakhiri **Enter**.
- Printer portable harus muncul di dialog cetak Android lewat aplikasi print service resminya; ukuran kertas di dialog diatur sama dengan label.
- Kebanyakan printer portable memakai **direct thermal** (tanpa ribbon). Label direct thermal biasa bisa pudar oleh panas dan sinar matahari. Gunakan bahan sintetis top-coated, dan untuk aset di area panas/luar ruang (power house, rooftop, kitchen hot line) cetak dengan printer desktop thermal transfer.
- Hitungan label: ±4.300 kamar + ±2.000 area + 15% cadangan ≈ 7.500 label.

### Perkiraan anggaran (2 tim)
| Pos | Perkiraan |
|---|---|
| 3 handheld | Rp 12–36 juta |
| 3 printer portable | Rp 9–24 juta |
| Label & media (±7.500 label) | Rp 2–5 juta |
| Printer desktop + media tahan lama (opsional) | Rp 8–15 juta |
| Perlengkapan lain | Rp 3–6 juta |
| **Total** | **± Rp 26–86 juta** tergantung kelas perangkat |

Setelah proyek, handheld dan printer tetap dipakai untuk inventarisasi tahunan, penggantian label, dan pelaporan kerusakan oleh Engineering.

## Lampiran: daftar periksa harian tim lapangan
- [ ] Handheld & printer terisi penuh, power bank siap
- [ ] Login {APP}, ukuran label di layar Pendataan sesuai roll
- [ ] Daftar kamar vacant dari FO sudah diterima
- [ ] Roll label & label stok kosong cukup untuk hari ini
- [ ] Setiap kamar ditutup dengan **Selesai kamar ini**
- [ ] Label rusak/terbuang dicatat dan dilaporkan
- [ ] Laporan singkat ke Asset Controller sebelum pulang
