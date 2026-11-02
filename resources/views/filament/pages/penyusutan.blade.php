<x-filament-panels::page>
    @php $rp = fn ($v) => 'Rp '.number_format((float) $v, 0, ',', '.'); $jt = fn ($v) => $v >= 1e9 ? 'Rp '.number_format($v / 1e9, 2, ',', '.').' M' : 'Rp '.number_format($v / 1e6, 1, ',', '.').' jt'; @endphp
    <div class="dp-wrap">
        <form class="ps-filter" method="get">
            <label>Per akhir bulan
                <select name="periode" onchange="this.form.submit()">
                    @foreach ($months as $k => $v)<option value="{{ $k }}" @selected($k === $periodeKey)>{{ $v }}</option>@endforeach
                </select>
            </label>
            <label>Kategori
                <select name="kategori" onchange="this.form.submit()">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $id => $n)<option value="{{ $id }}" @selected($id == $kategori)>{{ $n }}</option>@endforeach
                </select>
            </label>
            @if ($roots->count() > 1)
            <label>Lokasi
                <select name="lokasi" onchange="this.form.submit()">
                    <option value="">Semua lokasi</option>
                    @foreach ($roots as $id => $n)<option value="{{ $id }}" @selected($id == $lokasi)>{{ $n }}</option>@endforeach
                </select>
            </label>
            @endif
            @if ($canExport)<a class="sl-btn sl-btn-p" href="{{ $exportUrl }}">Ekspor Excel (daftar aset tetap + jurnal)</a>@endif
        </form>

        <div class="sl-kpis">
            <div><b>{{ $jt($kpi['cost']) }}</b><span>harga perolehan · {{ number_format($kpi['n'], 0, ',', '.') }} aset</span></div>
            <div><b>{{ $jt($kpi['accum']) }}</b><span>akumulasi penyusutan</span></div>
            <div><b>{{ $jt($kpi['book']) }}</b><span>nilai buku per {{ $asOf->translatedFormat('d M Y') }}</span></div>
            <div><b>{{ $jt($kpi['period']) }}</b><span>beban penyusutan {{ $asOf->translatedFormat('F Y') }}</span></div>
            <div><b>{{ $jt($kpi['ytd']) }}</b><span>beban penyusutan s.d. bulan ini ({{ $asOf->year }})</span></div>
            <div><b>{{ number_format($kpi['fully'], 0, ',', '.') }}</b><span>aset habis disusutkan (masih dipakai)</span></div>
        </div>

        @if ($kpi['n'] === 0)
            <p class="sl-empty">Belum ada aset dengan harga perolehan. Isi tab <b>Keuangan</b> pada aset (tanggal & biaya perolehan) atau impor lewat master aset.</p>
        @else
        @if ($kpi['fully'] > 0)
            <div class="ps-note">{{ number_format($kpi['fully'], 0, ',', '.') }} aset senilai perolehan {{ $jt($kpi['fullyCost']) }} sudah habis disusutkan tetapi masih dipakai. Tinjau masa manfaat kategori atau rencanakan penggantian.</div>
        @endif

        <h2 class="dp-h">Ringkasan per kategori</h2>
        <div class="ps-tbl-wrap"><table class="ps-tbl">
            <thead><tr><th>Kategori</th><th>Metode · masa manfaat</th><th class="r">Aset</th><th class="r">Harga perolehan</th><th class="r">Akumulasi</th><th class="r">Nilai buku</th><th class="r">Beban bulan ini</th><th class="r">Beban s.d. bulan ini</th></tr></thead>
            <tbody>
            @foreach ($byCat as $c)
                <tr><td>{{ $c['cat']?->name ?? '—' }}</td><td>{{ $methods[$c['cat']?->depreciation_method ?? 'straight_line'] ?? '' }} · {{ round(($c['cat']?->useful_life_months ?? 96) / 12, 1) }} th</td>
                    <td class="r">{{ $c['n'] }}</td><td class="r">{{ $rp($c['cost']) }}</td><td class="r">{{ $rp($c['accum']) }}</td><td class="r"><b>{{ $rp($c['book']) }}</b></td><td class="r">{{ $rp($c['period']) }}</td><td class="r">{{ $rp($c['ytd']) }}</td></tr>
            @endforeach
            </tbody>
            <tfoot><tr><td colspan="2">Jumlah</td><td class="r">{{ $kpi['n'] }}</td><td class="r">{{ $rp($kpi['cost']) }}</td><td class="r">{{ $rp($kpi['accum']) }}</td><td class="r">{{ $rp($kpi['book']) }}</td><td class="r">{{ $rp($kpi['period']) }}</td><td class="r">{{ $rp($kpi['ytd']) }}</td></tr></tfoot>
        </table></div>

        <h2 class="dp-h">Usulan jurnal penyusutan {{ $asOf->translatedFormat('F Y') }}</h2>
        <p class="sl-empty">Untuk dibukukan di sistem akuntansi. Nomor akun diisi di Data referensi › Kategori aset.</p>
        <div class="ps-tbl-wrap"><table class="ps-tbl">
            <thead><tr><th>Akun</th><th>Keterangan</th><th class="r">Debit</th><th class="r">Kredit</th></tr></thead>
            <tbody>
            @foreach ($byCat->where('period', '>', 0) as $c)
                <tr><td>{{ $c['cat']?->gl_expense_account ?: '(akun beban)' }}</td><td>Beban penyusutan · {{ $c['cat']?->name }}</td><td class="r">{{ $rp($c['period']) }}</td><td></td></tr>
                <tr><td>{{ $c['cat']?->gl_accum_account ?: '(akun akumulasi)' }}</td><td class="ps-ind">Akumulasi penyusutan · {{ $c['cat']?->name }}</td><td></td><td class="r">{{ $rp($c['period']) }}</td></tr>
            @endforeach
            </tbody>
            <tfoot><tr><td colspan="2">Jumlah</td><td class="r">{{ $rp($kpi['period']) }}</td><td class="r">{{ $rp($kpi['period']) }}</td></tr></tfoot>
        </table></div>

        <h2 class="dp-h">Daftar aset tetap <small class="ps-small">(50 nilai buku terbesar; daftar lengkap lewat Ekspor Excel)</small></h2>
        <div class="ps-tbl-wrap"><table class="ps-tbl">
            <thead><tr><th>Kode</th><th>Aset</th><th>Lokasi</th><th>Mulai susut</th><th class="r">Bulan</th><th class="r">Perolehan</th><th class="r">Akumulasi</th><th class="r">Nilai buku</th></tr></thead>
            <tbody>
            @foreach ($top as $r)
                @php $a = $r['asset']; $d = $r['d']; @endphp
                <tr><td><a href="{{ \App\Filament\Resources\Assets\AssetResource::getUrl('view', ['record' => $a]) }}">{{ $a->asset_code }}</a></td><td>{{ $a->name }}<small class="ps-small"> · {{ $a->category?->name }}</small></td>
                    <td>{{ $a->location?->full_path ?: $a->location?->name }}</td><td>{{ $d['start']?->translatedFormat('M Y') ?? '—' }}</td>
                    <td class="r">{{ $d['months'] }}/{{ $d['life'] }}</td><td class="r">{{ $rp($d['cost']) }}</td><td class="r">{{ $rp($d['accum']) }}</td><td class="r"><b>{{ $rp($d['book']) }}</b></td></tr>
            @endforeach
            </tbody>
        </table></div>
        @endif
    </div>
</x-filament-panels::page>
