# Work order
Perintah kerja untuk teknisi atau vendor, dari persetujuan biaya sampai verifikasi dan penutupan.

![Daftar work order](img/work-orders.png)
*Daftar work order dengan status, prioritas, PIC, dan target pulih.*

## Alur status
Draft → Menunggu persetujuan → Disetujui → Ditugaskan → Dikerjakan → Selesai teknisi → Terverifikasi → Ditutup. Bisa **Ditunda (hold)**, **Dibatalkan**, atau **Dibuka ulang**.

![Detail work order](img/work-orders-view.png)
*Detail work order: tombol tindakan sesuai status dan peran.*

## Siapa melakukan apa
- **Chief Engineering**: membuat, mengajukan, menugaskan PIC dan tim, memverifikasi, menutup.
- **Penyetuju** (sesuai Batas persetujuan): menyetujui estimasi biaya. Pembuat tidak dapat menyetujui pengajuannya sendiri.
- **Teknisi**: mulai, catat diagnosis, tindakan, uji fungsi, foto/bukti, waktu kerja dan biaya material.
- **Finance**: finalkan biaya, koreksi (reversal) bila perlu.
- **Front Office**: mengonfirmasi kamar siap dijual kembali bila sempat diblokir.

## Menyelesaikan pekerjaan (teknisi)
1. **Mulai** saat tiba di lokasi.
2. Isi **diagnosis**, **tindakan**, **hasil uji fungsi**. Lampirkan foto; bila tidak bisa, isi alasan pengecualian bukti.
3. Tambahkan **biaya** (material, jasa vendor) dan **waktu kerja**.
4. **Selesai**. Chief Engineering memverifikasi dan menetapkan status aset sesudahnya.

![Formulir work order](img/work-orders-create.png)
*Membuat work order langsung (mis. pekerjaan preventif).*
