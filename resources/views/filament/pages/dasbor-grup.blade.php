<x-filament-panels::page>
@php
    $c = collect($rows);
    $rp = fn ($v) => $v >= 1e9 ? 'Rp '.number_format($v / 1e9, 2, ',', '.').' M' : 'Rp '.number_format($v / 1e6, 1, ',', '.').' jt';
    $sum = fn ($k) => $c->sum($k);
    $labels = $c->pluck('code')->all();
    $charts = [
        'value' => ['type' => 'bar', 'data' => ['labels' => $labels, 'datasets' => [['label' => 'Harga perolehan', 'data' => $c->pluck('cost_basis'), 'backgroundColor' => 'P20', 'borderColor' => 'P', 'borderWidth' => 1], ['label' => 'Nilai buku', 'data' => $c->pluck('book'), 'backgroundColor' => 'P']]], 'opt' => ['money' => true]],
        'risk' => ['type' => 'bar', 'data' => ['labels' => $labels, 'datasets' => [['label' => '% aset berisiko D/E', 'data' => $c->pluck('risk_pct'), 'backgroundColor' => '#B42318']]]],
        'sla' => ['type' => 'bar', 'data' => ['labels' => $labels, 'datasets' => [['label' => 'SLA tepat waktu (%)', 'data' => $c->pluck('sla_pct'), 'backgroundColor' => '#1E7B45'], ['label' => 'Pendataan terverifikasi (%)', 'data' => $c->pluck('verified_pct'), 'backgroundColor' => 'A']]]],
        'cost' => ['type' => 'bar', 'data' => ['labels' => $labels, 'datasets' => [['label' => 'Biaya pemeliharaan 6 bulan', 'data' => $c->pluck('cost_6m'), 'backgroundColor' => 'A']]], 'opt' => ['money' => true]],
    ];
    $ok = fn ($v, $good, $bad, $rev = false) => $v === null ? '' : ($rev ? ($v <= $good ? 'dg-ok' : ($v >= $bad ? 'dg-bad' : 'dg-warn')) : ($v >= $good ? 'dg-ok' : ($v <= $bad ? 'dg-bad' : 'dg-warn')));
@endphp
<div class="dp-wrap">
    <div class="dg-head">
        <p class="sl-empty">{{ $c->count() }} perusahaan aktif dalam satu instalasi SIAGA. Angka diperbarui tiap 10 menit. Klik kode perusahaan untuk berpindah dan membuka dasbor pimpinannya.</p>
        <button type="button" class="sl-btn" wire:click="refreshData">Perbarui angka sekarang</button>
    </div>
    <div class="sl-kpis">
        <div><b>{{ number_format($sum('assets'), 0, ',', '.') }}</b><span>aset di {{ $c->count() }} perusahaan</span></div>
        @if ($fin)
        <div><b>{{ $rp($sum('cost_basis')) }}</b><span>harga perolehan aset tetap</span></div>
        <div><b>{{ $rp($sum('book')) }}</b><span>nilai buku (akhir bulan lalu)</span></div>
        @endif
        <div><b class="sl-bad">{{ number_format($sum('risk'), 0, ',', '.') }}</b><span>aset berisiko D/E</span></div>
        <div><b>{{ number_format($sum('open_wo'), 0, ',', '.') }}</b><span>WO terbuka ({{ $sum('overdue_wo') }} lewat target)</span></div>
        @if ($fin)<div><b>{{ $rp($sum('cost_6m')) }}</b><span>biaya pemeliharaan 6 bulan</span></div>@endif
    </div>

    <h2 class="dp-h">Perbandingan perusahaan</h2>
    <div class="ps-tbl-wrap"><table class="ps-tbl dg-tbl">
        <thead><tr><th>Perusahaan</th><th class="r">Aset</th><th class="r">Terverifikasi</th><th class="r">Berisiko D/E</th>
            @if ($fin)<th class="r">Nilai buku</th>@endif
            <th class="r">WO terbuka</th><th class="r">Lewat target</th><th class="r">SLA</th><th class="r">MTTR</th>
            @if ($fin)<th class="r">Biaya 6 bln</th>@endif
            <th class="r">Hari-kamar hilang (90 hr)</th><th>Opname terakhir</th><th class="r">Usulan hapus</th><th class="r">PM terlambat</th></tr></thead>
        <tbody>
        @foreach ($c as $r)
            <tr>
                <td><form method="post" action="{{ route('property.switch') }}" class="dg-sw">@csrf<input type="hidden" name="property_id" value="{{ $r['id'] }}"><input type="hidden" name="back" value="{{ \App\Filament\Pages\DasborPimpinan::getUrl(panel: 'admin', isAbsolute: false) }}"><button type="submit"><b>{{ $r['code'] }}</b> {{ $r['name'] }}</button></form></td>
                <td class="r">{{ number_format($r['assets'], 0, ',', '.') }}</td>
                <td class="r {{ $ok($r['verified_pct'], 90, 50) }}">{{ $r['verified_pct'] ?? '—' }}%</td>
                <td class="r {{ $ok($r['risk_pct'], 3, 8, true) }}">{{ $r['risk'] }} <small>({{ number_format($r['risk_pct'], 1, ',', '.') }}%)</small></td>
                @if ($fin)<td class="r">{{ $rp($r['book']) }}</td>@endif
                <td class="r">{{ $r['open_wo'] }}</td>
                <td class="r {{ $r['overdue_wo'] ? 'dg-bad' : 'dg-ok' }}">{{ $r['overdue_wo'] }}</td>
                <td class="r {{ $ok($r['sla_pct'], 90, 75) }}">{{ $r['sla_pct'] !== null ? $r['sla_pct'].'%' : '—' }}</td>
                <td class="r">{{ $r['mttr_h'] !== null ? number_format($r['mttr_h'], 1, ',', '.').' j' : '—' }}</td>
                @if ($fin)<td class="r">{{ $rp($r['cost_6m']) }}</td>@endif
                <td class="r">{{ $r['room_days'] }}</td>
                <td>{{ $r['so_date'] ? \Carbon\Carbon::parse($r['so_date'])->translatedFormat('d M Y').' · akurasi '.$r['so_acc'].'%' : 'belum pernah' }}</td>
                <td class="r">{{ $r['disposal_pending'] }}</td>
                <td class="r {{ $r['pm_overdue'] ? 'dg-bad' : '' }}">{{ $r['pm_overdue'] ?? '—' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table></div>
    <p class="sl-empty">Warna: hijau = sesuai target, kuning = perlu perhatian, merah = di bawah target (SLA ≥ 90%, aset berisiko ≤ 3%, terverifikasi ≥ 90%).</p>

    <div class="dp-grid">
        @if ($fin)<div class="arah-card dp-chart"><h3>Harga perolehan vs nilai buku</h3><div class="dp-canvas"><canvas data-chart="value"></canvas></div></div>@endif
        <div class="arah-card dp-chart"><h3>Porsi aset berisiko D/E</h3><div class="dp-canvas"><canvas data-chart="risk"></canvas></div></div>
        <div class="arah-card dp-chart"><h3>SLA & pendataan</h3><div class="dp-canvas"><canvas data-chart="sla"></canvas></div></div>
        @if ($fin)<div class="arah-card dp-chart"><h3>Biaya pemeliharaan 6 bulan</h3><div class="dp-canvas"><canvas data-chart="cost"></canvas></div></div>@endif
    </div>
</div>
@include('filament.pages.partials.charts', ['charts' => $charts])
</x-filament-panels::page>
