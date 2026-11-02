# Template aset kamar
Daftar isi standar per tipe kamar: jenis aset, jumlah per kamar, posisi (Kiri/Kanan), mode per unit atau jumlah. Template menjadi dasar generate aset dan checklist pendataan.

![Daftar template](img/room-asset-templates.png)
*Template per tipe kamar dengan versi dan status terbit.*

## Mengedit template
![Edit template](img/room-asset-templates-edit.png)
*Item template: kunci item, kategori, nama standar, jumlah, mode, posisi.*

- **Kunci item**: pengenal stabil lintas versi (mis. `tv-utama`). Jangan diubah setelah dipakai.
- **Jumlah** dan **Label posisi**: mis. jumlah 2 dengan posisi `Kiri, Kanan` menghasilkan dua aset "Lampu meja Kiri" dan "Lampu meja Kanan".
- **Mode**: *individual* = satu aset + satu label QR per unit; *quantity* = satu kelompok hitungan tanpa label per unit.
- **Usulan PM**: interval perawatan preventif yang berlaku setelah aset diaktifkan.

## Siklus template
1. **Draft**: disusun Asset Controller.
2. **Terbitkan**: diperiksa Chief Engineering; hanya template terbit yang bisa diterapkan.
3. **Buat versi baru**: untuk perubahan setelah terbit. Gunakan **Bandingkan** untuk melihat beda antar versi sebelum menerapkan ulang.

## Catatan
Template hasil impor master berstatus draft (kecuali dicentang terbit saat impor). Periksa jumlah, terutama untuk suite, sebelum diterbitkan.
