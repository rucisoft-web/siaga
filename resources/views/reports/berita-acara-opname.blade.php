<!doctype html><html lang="id"><head><meta charset="utf-8"><title>Berita acara {{ $take->number }}</title></head><body>
@include('reports._print')
<div class="pg">
    <h1>Berita Acara Stock Opname Aset Tetap</h1>
    <p class="no">{{ $property?->name }} · Nomor {{ $take->number }}</p>
    <table class="kv">
        <tr><td>Judul</td><td>{{ $take->title }}</td></tr>
        <tr><td>Cakupan lokasi</td><td>{{ $take->location?->full_path ?: ($take->location?->name ?? 'Semua lokasi') }}</td></tr>
        <tr><td>Tanggal cut-off</td><td>{{ $take->cutoff_date?->translatedFormat('d F Y') }}</td></tr>
        <tr><td>Dibuka oleh</td><td>{{ $take->creator?->name }} · {{ \App\Support\Fmt::dt($take->created_at) }}</td></tr>
        <tr><td>Status</td><td>{{ \App\Models\StockTake::STATUSES[$take->status] }}@if ($take->closed_at) · ditutup {{ \App\Support\Fmt::dt($take->closed_at) }} oleh {{ $take->closer?->name }}{{ $take->applied ? ' · hasil diterapkan ke register' : ' · register tidak diubah' }}@endif</td></tr>
    </table>
    <h2>Ringkasan hasil</h2>
    <table>
        <tr><th>Aset menurut register</th><th>Dihitung</th><th>Ditemukan di tempat</th><th>Pindah lokasi</th><th>Tidak ditemukan</th><th>Belum dihitung</th><th>Temuan tak terdaftar</th><th>Akurasi</th></tr>
        <tr><td class="r">{{ $sum['total'] }}</td><td class="r">{{ $sum['done'] }}</td><td class="r">{{ $sum['counts']['found'] }}</td><td class="r">{{ $sum['counts']['moved'] }}</td><td class="r">{{ $sum['counts']['not_found'] }}</td><td class="r">{{ $sum['counts']['pending'] }}</td><td class="r">{{ $sum['counts']['unregistered'] }}</td><td class="r">{{ $sum['accuracy'] ?? '—' }}%</td></tr>
    </table>
    <h2>Rekap per lokasi</h2>
    <table><tr><th>Lokasi</th><th class="r">Register</th><th class="r">Ditemukan</th><th class="r">Tidak ditemukan</th></tr>
        @foreach ($byLocation as $l)<tr><td>{{ $l->loc }}</td><td class="r">{{ $l->n }}</td><td class="r">{{ $l->ok }}</td><td class="r">{{ $l->nf }}</td></tr>@endforeach
    </table>
    <h2>Daftar aset tidak ditemukan ({{ $notFound->count() }})</h2>
    @if ($notFound->isEmpty())<p>Nihil.</p>@else
    <table><tr><th>No</th><th>Kode aset</th><th>Nama</th><th>Lokasi tercatat</th></tr>
        @foreach ($notFound as $i => $it)<tr><td>{{ $i + 1 }}</td><td>{{ $it->asset?->asset_code }}</td><td>{{ $it->asset?->name }}</td><td>{{ $it->expectedLocation?->full_path }}</td></tr>@endforeach
    </table>@endif
    <h2>Daftar aset ditemukan di lokasi lain ({{ $moved->count() }})</h2>
    @if ($moved->isEmpty())<p>Nihil.</p>@else
    <table><tr><th>No</th><th>Kode aset</th><th>Nama</th><th>Lokasi tercatat</th><th>Lokasi ditemukan</th></tr>
        @foreach ($moved as $i => $it)<tr><td>{{ $i + 1 }}</td><td>{{ $it->asset?->asset_code }}</td><td>{{ $it->asset?->name }}</td><td>{{ $it->expectedLocation?->full_path }}</td><td>{{ $it->foundLocation?->full_path }}</td></tr>@endforeach
    </table>@endif
    <h2>Temuan aset tidak terdaftar ({{ $unregistered->count() }})</h2>
    @if ($unregistered->isEmpty())<p>Nihil.</p>@else
    <table><tr><th>No</th><th>Uraian</th><th>Lokasi</th><th>Catatan</th></tr>
        @foreach ($unregistered as $i => $it)<tr><td>{{ $i + 1 }}</td><td>{{ $it->description }}</td><td>{{ $it->foundLocation?->full_path }}</td><td>{{ $it->note }}</td></tr>@endforeach
    </table>@endif
    <p style="margin-top:14px">Demikian berita acara ini dibuat dengan sebenarnya. Aset tidak ditemukan ditindaklanjuti dengan pencarian ulang atau usulan penghapusan sesuai kebijakan perusahaan.</p>
    <div class="sign">
        <div>Petugas pelaksana<small>Asset Controller</small></div>
        <div>Mengetahui<small>Kepala departemen / pemilik lokasi</small></div>
        <div>Menyetujui<small>Finance / Chief Accountant</small></div>
    </div>
</div>
</body></html>
