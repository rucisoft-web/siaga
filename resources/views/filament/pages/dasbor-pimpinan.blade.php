@php
    use App\Support\Fmt;
    use App\Filament\Resources\Assets\AssetResource as AR;
    $k = $d['kpi'];
    $jt = fn ($v) => $v >= 1e9 ? 'Rp '.Fmt::num($v / 1e9, 1).' M' : ($v >= 1e6 ? 'Rp '.Fmt::num($v / 1e6, 1).' jt' : Fmt::rp($v));
    $surveyDone = array_sum($d['survey']['done']);
    $surveyAll = $surveyDone + array_sum($d['survey']['todo']);
    // sorotan otomatis untuk pimpinan
    $notes = [];
    if ($k['risk_count']) {
        $notes[] = ['warn', Fmt::num($k['risk_count']).' aset dalam kondisi D/E atau tidak beroperasi'.($financial ? ', nilai ganti ±'.$jt($k['risk_value']) : '').'. Prioritaskan yang kritis (daftar di bawah).'];
    }
    if ($k['sla_pct'] !== null) {
        $notes[] = [$k['sla_pct'] >= 90 ? 'ok' : 'warn', 'Pemulihan sesuai target (SLA) '.$k['sla_pct'].'% dalam 90 hari ('.$k['sla_note'].'). Target yang lazim ≥ 90%.'];
    }
    $top = $d['top_assets'][0] ?? null;
    if ($financial && $top && $top['ratio'] !== null && $top['ratio'] >= 25) {
        $notes[] = ['warn', 'Biaya perbaikan '.$top['name'].' ('.$top['where'].') sudah '.$top['ratio'].'% dari harga perolehan dalam 6 bulan: pertimbangkan penggantian.'];
    }
    if ($k['repeat']) {
        $notes[] = ['info', $k['repeat'].' aset rusak berulang (≥ 2 kali dalam 90 hari): kaji akar masalah atau rencana penggantian.'];
    }
    $old = array_sum(array_slice($d['backlog_age']['values'], 3));
    if ($old) {
        $notes[] = ['warn', $old.' work order terbuka lebih dari 14 hari: minta rencana penyelesaian.'];
    }
    if ($k['room_days_90'] > 0) {
        $notes[] = ['info', Fmt::num($k['room_days_90'], 1).istilah(' hari-kamar/ruang tidak bisa dipakai (blokir perbaikan) dalam 90 hari.')];
    }
    if ($surveyAll) {
        $notes[] = ['info', 'Pendataan: '.$surveyDone.' dari '.$surveyAll.istilah(' kamar/ruang selesai (').round($surveyDone / $surveyAll * 100).'%).'];
    }
@endphp
<x-filament-panels::page>
<div class="arah dp">
    <div class="arah-card dp-head">
        <div>
            <div class="arah-muted">{{ $property?->name }} · data per {{ Fmt::dt(now(), 'd M Y H:i') }} {{ \App\Support\Branding::timezoneLabel() }}</div>
            @if (count($buildings) > 1)
                <div class="dp-pills" role="tablist" aria-label="Filter lokasi">
                    <a href="{{ \App\Filament\Pages\DasborPimpinan::getUrl() }}" class="dp-pill {{ $lokasi ? '' : 'on' }}">Semua lokasi</a>
                    @foreach ($buildings as $id => $name)
                        <a href="{{ \App\Filament\Pages\DasborPimpinan::getUrl(['lokasi' => $id]) }}" class="dp-pill {{ $lokasi === $id ? 'on' : '' }}">{{ $name }}</a>
                    @endforeach
                </div>
            @endif
        </div>
        <button type="button" class="arah-btn" onclick="window.print()">Cetak / PDF</button>
    </div>

    @if ($notes)
        <div class="arah-card dp-notes">
            <h3>Sorotan untuk pimpinan</h3>
            <ul>@foreach ($notes as [$lvl, $txt])<li class="dp-n-{{ $lvl }}">{{ $txt }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- 1. KONDISI & RISIKO --}}
    <h2 class="dp-h">1 · Kondisi &amp; risiko aset</h2>
    <div class="arah-grid arah-grid-4">
        <div class="arah-stat"><span class="label">Aset terdaftar</span><span class="value">{{ Fmt::num($k['assets']) }}</span><span class="sub">{{ Fmt::num($k['active']) }} aktif · {{ $k['verified_pct'] ?? 0 }}% terverifikasi fisik</span></div>
        <div class="arah-stat"><span class="label">Nilai aset aktif</span><span class="value">{{ $financial ? $jt($k['value']) : '—' }}</span><span class="sub">harga perolehan tercatat</span></div>
        <div class="arah-stat"><span class="label">Aset berisiko</span><span class="value" style="color:var(--k-merah)">{{ Fmt::num($k['risk_count']) }}</span><span class="sub">kondisi D/E atau tidak beroperasi{{ $financial ? ' · nilai ganti '.$jt($k['risk_value']) : '' }}</span></div>
        <div class="arah-stat"><span class="label">Progres pendataan</span><span class="value">{{ $surveyAll ? round($surveyDone / $surveyAll * 100) : 0 }}%</span><span class="sub">{{ $surveyDone }} dari {{ $surveyAll }} {{ istilah('kamar/ruang') }}</span></div>
    </div>
    <div class="dp-grid">
        <div class="arah-card dp-chart"><h3>Kondisi aset per lokasi</h3><p class="arah-muted">Jumlah aset menurut nilai kondisi A–E</p><div class="dp-canvas"><canvas data-chart="cond_building"></canvas></div></div>
        <div class="arah-card dp-chart"><h3>Kondisi per kategori (8 terbanyak)</h3><p class="arah-muted">Kategori dengan porsi D/E besar = kandidat program penggantian</p><div class="dp-canvas"><canvas data-chart="cond_category"></canvas></div></div>
        @if ($financial)
            <div class="arah-card dp-chart"><h3>Nilai ganti aset berisiko per kategori</h3><p class="arah-muted">Estimasi biaya bila aset D/E / tidak beroperasi diganti</p><div class="dp-canvas"><canvas data-chart="risk_value"></canvas></div></div>
        @endif
        <div class="arah-card dp-chart"><h3>Progres pendataan</h3><p class="arah-muted">{{ istilah('Kamar/ruang selesai vs belum per lokasi, dan kurva 30 hari') }}</p><div class="dp-canvas dp-canvas-half"><canvas data-chart="survey"></canvas></div><div class="dp-canvas dp-canvas-half"><canvas data-chart="survey_curve"></canvas></div></div>
    </div>

    {{-- 2. KINERJA PERBAIKAN --}}
    <h2 class="dp-h">2 · Kinerja perbaikan</h2>
    <div class="arah-grid arah-grid-4">
        <div class="arah-stat"><span class="label">Work order terbuka</span><span class="value">{{ $k['open_wo'] }}</span><span class="sub" style="color:var(--k-merah)">{{ $k['overdue_wo'] }} lewat target</span></div>
        <div class="arah-stat"><span class="label">Pemulihan sesuai SLA</span><span class="value">{{ $k['sla_pct'] === null ? 'N/A' : $k['sla_pct'].'%' }}</span><span class="sub">{{ $k['sla_note'] }} · 90 hari</span></div>
        <div class="arah-stat"><span class="label">MTTR rata-rata</span><span class="value">{{ $k['mttr_h'] === null ? 'N/A' : Fmt::num($k['mttr_h'], 1).' jam' }}</span><span class="sub">laporan → layanan pulih (90 hari)</span></div>
        <div class="arah-stat"><span class="label">Kerusakan berulang</span><span class="value">{{ $k['repeat'] }}</span><span class="sub">aset ≥ 2 kali rusak dalam 90 hari</span></div>
    </div>
    <div class="dp-grid">
        <div class="arah-card dp-chart dp-wide"><h3>Tren mingguan: work order masuk vs selesai</h3><p class="arah-muted">12 minggu terakhir. Garis masuk di atas batang selesai berarti backlog bertambah.</p><div class="dp-canvas"><canvas data-chart="weekly"></canvas></div></div>
        <div class="arah-card dp-chart"><h3>Backlog per status</h3><div class="dp-canvas"><canvas data-chart="backlog_status"></canvas></div></div>
        <div class="arah-card dp-chart"><h3>Umur backlog</h3><div class="dp-canvas"><canvas data-chart="backlog_age"></canvas></div></div>
        <div class="arah-card dp-chart"><h3>SLA pemulihan per prioritas (90 hari)</h3><div class="dp-canvas"><canvas data-chart="sla"></canvas></div></div>
        <div class="arah-card dp-chart"><h3>MTTR per kategori (jam)</h3><div class="dp-canvas"><canvas data-chart="mttr"></canvas></div></div>
    </div>

    {{-- 3. BIAYA & DAMPAK --}}
    <h2 class="dp-h">3 · Biaya &amp; dampak</h2>
    <div class="arah-grid arah-grid-4">
        <div class="arah-stat"><span class="label">Biaya pemeliharaan 30 hari</span><span class="value">{{ $financial ? $jt($k['cost_30']) : '—' }}</span><span class="sub">material, vendor & tenaga internal</span></div>
        <div class="arah-stat"><span class="label">Biaya 6 bulan</span><span class="value">{{ $financial ? $jt($k['cost_6m']) : '—' }}</span><span class="sub">termasuk riwayat</span></div>
        <div class="arah-stat"><span class="label">{{ istilah('Hari-kamar/ruang hilang') }}</span><span class="value">{{ Fmt::num($k['room_days_90'], 1) }}</span><span class="sub">blokir perbaikan, 90 hari</span></div>
        <div class="arah-stat"><span class="label">Laporan terbuka</span><span class="value">{{ $k['requests_open'] }}</span><span class="sub">belum selesai / belum ditriase</span></div>
    </div>
    <div class="dp-grid">
        @if ($financial)
            <div class="arah-card dp-chart dp-wide"><h3>Biaya pemeliharaan per bulan</h3><p class="arah-muted">Menurut jenis biaya, 6 bulan terakhir</p><div class="dp-canvas"><canvas data-chart="cost_monthly"></canvas></div></div>
            <div class="arah-card dp-chart"><h3>Biaya per kategori aset</h3><div class="dp-canvas"><canvas data-chart="cost_category"></canvas></div></div>
        @endif
        <div class="arah-card dp-chart"><h3>{{ istilah('Hari-kamar/ruang tidak terpakai per bulan') }}</h3><p class="arah-muted">{{ istilah('Kamar/ruang diblokir karena perbaikan') }}</p><div class="dp-canvas"><canvas data-chart="room_days"></canvas></div></div>
    </div>

    <div class="dp-grid">
        @if ($financial)
            <div class="arah-card dp-wide">
                <h3>10 aset dengan biaya perbaikan tertinggi (6 bulan)</h3>
                <p class="arah-muted">Rasio biaya terhadap harga perolehan ≥ 25% menjadi sinyal "perbaiki atau ganti".</p>
                <div class="dp-table"><table class="arah-table">
                    <thead><tr><th>Aset</th><th>Lokasi</th><th class="num">Perbaikan</th><th class="num">Biaya</th><th class="num">Harga perolehan</th><th class="num">Rasio</th></tr></thead>
                    <tbody>@forelse ($d['top_assets'] as $r)
                        <tr><td><a href="{{ AR::getUrl('view', ['record' => $r['id']]) }}">{{ $r['name'] }}</a><div class="arah-muted">{{ $r['code'] }}</div></td><td>{{ $r['where'] }}</td><td class="num">{{ $r['count'] }}×</td>
                            <td class="num">{{ Fmt::rp($r['cost']) }}</td><td class="num">{{ $r['acq'] ? Fmt::rp($r['acq']) : '—' }}</td>
                            <td class="num">@if ($r['ratio'] !== null)<span class="dp-ratio {{ $r['ratio'] >= 25 ? 'hi' : '' }}">{{ $r['ratio'] }}%</span>@else — @endif</td></tr>
                    @empty <tr><td colspan="6" class="arah-muted">Belum ada biaya tercatat.</td></tr> @endforelse</tbody>
                </table></div>
            </div>
        @endif
        <div class="arah-card">
            <h3>Kerusakan berulang (90 hari)</h3>
            <div class="dp-table"><table class="arah-table">
                <thead><tr><th>Aset</th><th>Lokasi</th><th class="num">Kali</th><th>Terakhir</th></tr></thead>
                <tbody>@forelse ($d['repeat'] as $r)
                    <tr><td><a href="{{ AR::getUrl('view', ['record' => $r['id']]) }}">{{ $r['name'] }}</a></td><td>{{ $r['where'] }}</td><td class="num">{{ $r['count'] }}</td><td>{{ $r['last'] }}</td></tr>
                @empty <tr><td colspan="4" class="arah-muted">Tidak ada.</td></tr> @endforelse</tbody>
            </table></div>
        </div>
        <div class="arah-card">
            <h3>Aset berisiko yang perlu keputusan</h3>
            <div class="dp-table"><table class="arah-table">
                <thead><tr><th>Aset</th><th>Lokasi</th><th>Kondisi</th>@if ($financial)<th class="num">Nilai</th>@endif</tr></thead>
                <tbody>@forelse ($d['risk_list'] as $r)
                    <tr><td><a href="{{ AR::getUrl('view', ['record' => $r['id']]) }}">{{ $r['name'] }}</a></td><td>{{ $r['where'] }}</td>
                        <td><span class="dp-grade g{{ $r['grade'] ?: 'x' }}">{{ $r['grade'] ?: '—' }}</span> {{ $r['status'] !== 'serviceable' ? str_replace('_', ' ', $r['status']) : '' }}</td>
                        @if ($financial)<td class="num">{{ $r['value'] ? Fmt::rp($r['value']) : '—' }}</td>@endif</tr>
                @empty <tr><td colspan="4" class="arah-muted">Tidak ada aset berisiko.</td></tr> @endforelse</tbody>
            </table></div>
        </div>
    </div>
    <p class="arah-muted dp-foot">Sumber: register aset, hasil pendataan, laporan kerusakan, work order & biaya di aplikasi ini. Pada situs demo, angka adalah data contoh/proyeksi.</p>
</div>

@php
    $grades = \App\Services\LeadershipService::GRADES;
    $gradeColors = ['A' => '#1E7B45', 'B' => '#7FB069', 'C' => '#E0A526', 'D' => '#D9622B', 'E' => '#B42318', '?' => '#B7BEC9'];
    $stack = fn ($block) => collect($block['series'])->map(fn ($v, $g) => ['label' => $grades[$g], 'data' => $v, 'backgroundColor' => $gradeColors[$g]])->filter(fn ($s) => array_sum($s['data']) > 0)->values()->all();
    $charts = [
        'cond_building' => ['type' => 'bar', 'data' => ['labels' => $d['condition_by_building']['labels'], 'datasets' => $stack($d['condition_by_building'])], 'opt' => ['stacked' => true, 'horizontal' => true]],
        'cond_category' => ['type' => 'bar', 'data' => ['labels' => $d['condition_by_category']['labels'], 'datasets' => $stack($d['condition_by_category'])], 'opt' => ['stacked' => true, 'horizontal' => true]],
        'risk_value' => ['type' => 'bar', 'data' => ['labels' => $d['risk_value_by_category']['labels'], 'datasets' => [['label' => 'Nilai ganti (Rp)', 'data' => $d['risk_value_by_category']['values'], 'backgroundColor' => '#D9622B']]], 'opt' => ['horizontal' => true, 'money' => true]],
        'survey' => ['type' => 'bar', 'data' => ['labels' => $d['survey']['labels'], 'datasets' => [['label' => 'Selesai', 'data' => $d['survey']['done'], 'backgroundColor' => 'P'], ['label' => 'Belum', 'data' => $d['survey']['todo'], 'backgroundColor' => '#C9CFD8']]], 'opt' => ['stacked' => true, 'horizontal' => true]],
        'survey_curve' => ['type' => 'line', 'data' => ['labels' => $d['survey']['curve']['labels'] ?? [], 'datasets' => [['label' => istilah('Kumulatif kamar/ruang selesai'), 'data' => $d['survey']['curve']['values'] ?? [], 'borderColor' => 'P', 'backgroundColor' => 'P20', 'fill' => true, 'tension' => .3, 'pointRadius' => 0]]]],
        'weekly' => ['type' => 'bar', 'data' => ['labels' => $d['weekly']['labels'], 'datasets' => [
            ['type' => 'line', 'label' => 'Masuk', 'data' => $d['weekly']['in'], 'borderColor' => '#B42318', 'backgroundColor' => '#B42318', 'tension' => .3],
            ['label' => 'Selesai', 'data' => $d['weekly']['out'], 'backgroundColor' => 'P']]]],
        'backlog_status' => ['type' => 'bar', 'data' => ['labels' => $d['backlog_status']['labels'], 'datasets' => [['label' => 'Work order', 'data' => $d['backlog_status']['values'], 'backgroundColor' => 'P']]], 'opt' => ['horizontal' => true]],
        'backlog_age' => ['type' => 'bar', 'data' => ['labels' => $d['backlog_age']['labels'], 'datasets' => [['label' => 'Work order', 'data' => $d['backlog_age']['values'], 'backgroundColor' => ['#1E7B45', '#7FB069', '#E0A526', '#D9622B', '#B42318']]]]],
        'sla' => ['type' => 'bar', 'data' => ['labels' => $d['sla_priority']['labels'], 'datasets' => [['label' => 'Tepat waktu', 'data' => $d['sla_priority']['met'], 'backgroundColor' => '#1E7B45'], ['label' => 'Terlambat', 'data' => $d['sla_priority']['missed'], 'backgroundColor' => '#B42318']]], 'opt' => ['stacked' => true]],
        'mttr' => ['type' => 'bar', 'data' => ['labels' => $d['mttr_category']['labels'], 'datasets' => [['label' => 'Jam', 'data' => $d['mttr_category']['values'], 'backgroundColor' => 'A']]], 'opt' => ['horizontal' => true]],
        'cost_monthly' => ['type' => 'bar', 'data' => ['labels' => $d['cost_monthly']['labels'], 'datasets' => collect($d['cost_monthly']['series'])->values()->map(fn ($v, $i) => ['label' => array_keys($d['cost_monthly']['series'])[$i], 'data' => $v, 'backgroundColor' => ['P', 'A', '#7FB069', '#B7BEC9'][$i] ?? '#999'])->all()], 'opt' => ['stacked' => true, 'money' => true]],
        'cost_category' => ['type' => 'doughnut', 'data' => ['labels' => $d['cost_category']['labels'], 'datasets' => [['data' => $d['cost_category']['values'], 'backgroundColor' => ['P', 'A', '#7FB069', '#D9622B', '#34506E', '#E0A526', '#8E6BB8', '#B7BEC9']]]], 'opt' => ['money' => true]],
        'room_days' => ['type' => 'bar', 'data' => ['labels' => $d['room_days']['labels'], 'datasets' => [['label' => istilah('Hari-kamar/ruang'), 'data' => $d['room_days']['values'], 'backgroundColor' => '#D9622B']]]],
    ];
@endphp
@include('filament.pages.partials.charts', ['charts' => $charts])
</x-filament-panels::page>
