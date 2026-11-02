# Laporan kerusakan
Laporan gangguan dari departemen mana pun (FO, HK, F&B), ditriase Engineering, lalu dikerjakan lewat work order.

![Daftar laporan](img/service-requests.png)
*Daftar laporan dengan prioritas, batas respons SLA, dan status.*

## Membuat laporan
![Formulir laporan di HP](img/service-requests-create-hp.png)
*Lapor dari HP: pindai QR aset atau pilih lokasi, isi gejala.*

1. Pindai QR aset (paling cepat) atau pilih **aset/lokasi**.
2. Pilih **kategori** dan isi **gejala / keluhan** dengan jelas (apa, sejak kapan, seberapa parah).
3. Pilih **dampak operasional** (mis. kamar tidak bisa dijual, acara terganggu).
4. Centang **Ada bahaya keselamatan** bila ada listrik terbuka, kebocoran gas, alarm. Laporan otomatis P1.
5. Kirim. Laporan yang sama untuk aset yang sama dalam waktu dekat ditandai sebagai kemungkinan duplikat.

## Detail & triase
![Detail laporan](img/service-requests-view.png)
*Detail laporan: tindakan triase di kanan atas.*

Engineering melakukan:
- **Akui laporan**: menghentikan jam SLA respons.
- **Triase**: tetapkan prioritas P1–P4, catatan, perlu vendor, usulan kamar tidak dijual sementara.
- **Buat work order** atau **Tautkan ke work order yang ada** (gabungkan laporan sejenis).
- **Tandai duplikat** atau **Tolak laporan** (alasan dikirim ke pelapor).

| Prioritas | Contoh |
|---|---|
| P1 Darurat | bahaya keselamatan, banjir, listrik padam area tamu |
| P2 Mendesak | kamar tidak bisa dijual, AC mati di kamar terisi |
| P3 Normal | gangguan dengan layanan alternatif |
| P4 Terencana | kosmetik, bisa dijadwalkan |
