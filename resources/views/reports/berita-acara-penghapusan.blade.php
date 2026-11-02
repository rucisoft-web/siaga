<!doctype html><html lang="id"><head><meta charset="utf-8"><title>Berita acara {{ $d->number }}</title></head><body>
@include('reports._print')
@php $rp = fn ($v) => 'Rp '.number_format((float) $v, 0, ',', '.'); @endphp
<div class="pg">
    <h1>Berita Acara Penghapusan Aset Tetap</h1>
    <p class="no">{{ $property?->name }} · Nomor {{ $d->number }} · {{ \App\Models\Disposal::STATUSES[$d->status] }}</p>
    <h2>Data aset</h2>
    <table class="kv">
        <tr><td>Kode aset</td><td>{{ $d->asset?->asset_code }}</td></tr>
        <tr><td>Nama aset</td><td>{{ $d->asset?->name }} ({{ $d->asset?->category?->name }})</td></tr>
        <tr><td>Merek / model / no. seri</td><td>{{ collect([$d->asset?->brand, $d->asset?->model, $d->asset?->serial_number])->filter()->join(' / ') ?: '-' }}</td></tr>
        <tr><td>Lokasi terakhir</td><td>{{ $d->asset?->location?->full_path ?: $d->asset?->location?->name }}</td></tr>
        @if ($fin)
        <tr><td>Harga perolehan</td><td>{{ $rp($d->acquisition_cost) }}</td></tr>
        <tr><td>Akumulasi penyusutan</td><td>{{ $rp($d->accumulated) }}</td></tr>
        <tr><td>Nilai buku saat diusulkan</td><td>{{ $rp($d->book_value) }}</td></tr>
        @endif
    </table>
    <h2>Penghapusan</h2>
    <table class="kv">
        <tr><td>Cara penghapusan</td><td>{{ \App\Models\Disposal::METHODS[$d->method] }}</td></tr>
        <tr><td>Alasan</td><td>{{ $d->reason }}</td></tr>
        @if ($d->justification)<tr><td>Kajian / justifikasi</td><td>{{ $d->justification }}</td></tr>@endif
        <tr><td>Diusulkan oleh</td><td>{{ $d->requester?->name }} · {{ $d->proposed_date?->translatedFormat('d F Y') }}</td></tr>
        <tr><td>Keputusan</td><td>@if ($d->approver){{ $d->status === 'rejected' ? 'Ditolak' : 'Disetujui' }} oleh {{ $d->approver->name }} · {{ \App\Support\Fmt::dt($d->approved_at) }}{{ $d->decision_note ? ' · '.$d->decision_note : '' }}@else menunggu @endif</td></tr>
        @if ($d->completed_date)<tr><td>Dilaksanakan</td><td>{{ $d->completed_date->translatedFormat('d F Y') }} oleh {{ $d->completer?->name }}{{ $d->document_ref ? ' · dok. '.$d->document_ref : '' }}</td></tr>@endif
        @if ($fin && $d->proceeds !== null)
        <tr><td>Hasil penjualan</td><td>{{ $rp($d->proceeds) }}{{ $d->buyer ? ' · pembeli: '.$d->buyer : '' }}</td></tr>
        <tr><td>Laba (rugi) pelepasan</td><td>{{ $rp($d->gainLoss()) }}</td></tr>
        @endif
    </table>
    @if ($fin && $d->status === 'completed')
    <h2>Usulan jurnal pelepasan</h2>
    <table><tr><th>Akun</th><th class="r">Debit</th><th class="r">Kredit</th></tr>
        <tr><td>Akumulasi penyusutan · {{ $d->asset?->category?->gl_accum_account }}</td><td class="r">{{ $rp($d->accumulated) }}</td><td></td></tr>
        @if ($d->proceeds)<tr><td>Kas / piutang (hasil penjualan)</td><td class="r">{{ $rp($d->proceeds) }}</td><td></td></tr>@endif
        @if ($d->gainLoss() < 0)<tr><td>Rugi pelepasan aset tetap</td><td class="r">{{ $rp(abs($d->gainLoss())) }}</td><td></td></tr>@endif
        <tr><td>Aset tetap · {{ $d->asset?->category?->gl_asset_account }}</td><td></td><td class="r">{{ $rp($d->acquisition_cost) }}</td></tr>
        @if ($d->gainLoss() > 0)<tr><td>Laba pelepasan aset tetap</td><td></td><td class="r">{{ $rp($d->gainLoss()) }}</td></tr>@endif
    </table>
    @endif
    <div class="sign">
        <div>Diusulkan oleh<small>{{ $d->requester?->name }}</small></div>
        <div>Disetujui oleh<small>{{ $d->approver?->name ?? '…………' }}</small></div>
        <div>Pelaksana<small>{{ $d->completer?->name ?? '…………' }}</small></div>
    </div>
</div>
</body></html>
