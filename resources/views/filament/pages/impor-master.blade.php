<x-filament-panels::page>
<div class="arah">
    <div class="arah-grid arah-grid-2">
        <div class="arah-card">
            <h3>1 · Pilih file master aset</h3>
            <p class="arah-muted">Workbook .xlsx dengan sheet <b>Daftar Jenis Kamar</b>, <b>Rujukan Aset Kamar</b>, <b>Nama Ruang Meeting</b>, <b>Master Aset Gabungan</b>, dan (opsional) <b>Daftar Kamar</b>. Judul kolom dibaca dari baris pertama tiap sheet.</p>
            <label class="arah-btn" style="cursor:pointer; margin-top:.4rem">Pilih file Excel
                <input type="file" accept=".xlsx" wire:model="file" style="display:none">
            </label>
            <span class="arah-muted" wire:loading wire:target="file">Mengunggah…</span>
            @if ($file)<span class="arah-muted">{{ $file->getClientOriginalName() }}</span>@endif
            @error('file')<div class="ac-scan-msg err">{{ $message }}</div>@enderror
            <label style="display:flex; gap:.5rem; align-items:center; margin-top:.8rem">
                <input type="checkbox" wire:model="publish"> Langsung terbitkan template kamar (bisa di-generate)
            </label>
            <div class="ac-actions">
                <button type="button" class="arah-btn" wire:click="check" wire:loading.attr="disabled">2 · Uji coba (tidak menyimpan)</button>
                <button type="button" class="arah-btn arah-btn-primary" wire:click="import" wire:loading.attr="disabled" wire:confirm="Simpan hasil impor ke database?">3 · Impor</button>
            </div>
            <div wire:loading wire:target="check,import" class="arah-muted">Membaca workbook…</div>
            @if ($error)<div class="ac-scan-msg err">{{ $error }}</div>@endif
        </div>
        <div class="arah-card">
            <h3>Hasil {{ $result ? ($result['dry_run'] ? '· uji coba' : '· tersimpan') : '' }}</h3>
            @if (! $result)
                <div class="arah-empty">Jalankan uji coba untuk melihat apa yang akan dibuat.</div>
            @else
                <table class="arah-table">
                    <tbody>
                    @foreach ($this->labels as $k => $l)
                        <tr><td>{{ $l }}</td><td class="num"><b>{{ $result['summary'][$k] ?? 0 }}</b></td></tr>
                    @endforeach
                    </tbody>
                </table>
                @foreach ($result['warnings'] as $w)<div class="ac-scan-msg err">{{ $w }}</div>@endforeach
                @if ($result['dry_run'])
                    <div class="ac-scan-msg ok">Belum ada yang disimpan. Bila angka sudah sesuai, tekan <b>Impor</b>.</div>
                @else
                    <div class="ac-scan-msg ok">Tersimpan. Lanjutkan: periksa <b>Template kamar</b> → terbitkan → <b>Generate aset kamar</b> → <b>Pendataan aset</b>.</div>
                @endif
            @endif
        </div>
    </div>
    <div class="arah-card" style="margin-top:1rem">
        <h3>Bagaimana data dipetakan</h3>
        <table class="arah-table">
            <thead><tr><th>Sheet</th><th>Menjadi</th></tr></thead>
            <tbody>
                <tr><td>Daftar Jenis Kamar</td><td>Tipe kamar (kode dibuat otomatis dari nama).</td></tr>
                <tr><td>Rujukan Aset Kamar</td><td>Template isi kamar per tipe. Satuan titik/set/pcs/lot dan kelompok Linen menjadi <b>item hitungan</b> (tanpa label QR per unit); lainnya per unit berlabel QR.</td></tr>
                <tr><td>Nama Ruang Meeting</td><td>Ruang meeting (venue) di bawah gedung perkiraan.</td></tr>
                <tr><td>Master Aset Gabungan</td><td>Area umum & ruang meeting per gedung, dengan item <b>estimasi</b> (jumlah fisik dihitung petugas saat pendataan). Baris "Aset Kamar" dilewati karena sudah tercakup template.</td></tr>
                <tr><td>Daftar Kamar (opsional)</td><td>Kolom: Nomor kamar · Jenis kamar · Gedung · Lantai. Membuat lantai & kamar.</td></tr>
            </tbody>
        </table>
        <p class="arah-muted">Aman diulang: data dengan nama/kode yang sama dilewati, jadi file bisa dilengkapi lalu diimpor lagi.</p>
    </div>
</div>
</x-filament-panels::page>
