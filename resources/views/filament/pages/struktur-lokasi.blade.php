<x-filament-panels::page>
    @php $openIds = collect($crumbs)->pluck('id')->all(); @endphp
    <div class="sl-wrap">
        <div class="sl-kpis">
            <div><b>{{ number_format($total['loc'], 0, ',', '.') }}</b><span>lokasi</span></div>
            <div><b>{{ number_format($total['n'], 0, ',', '.') }}</b><span>aset berlokasi</span></div>
            <div><b class="sl-bad">{{ number_format($total['risk'], 0, ',', '.') }}</b><span>aset berisiko (D/E)</span></div>
            <div><b>{{ number_format($total['wo'], 0, ',', '.') }}</b><span>work order terbuka</span></div>
        </div>

        <form method="get" class="ps-filter sl-filter">
            <div class="so-tabs sl-views">
                <a href="{{ $link(['tampilan' => 'pohon']) }}" @class(['on' => $tampilan !== 'matriks'])>Pohon lokasi</a>
                <a href="{{ $link(['tampilan' => 'matriks', 'pilih' => null]) }}" @class(['on' => $tampilan === 'matriks'])>Matriks lokasi × jenis barang</a>
            </div>
            <input type="hidden" name="tampilan" value="{{ $tampilan }}">
            @if ($pilih && $tampilan !== 'matriks')<input type="hidden" name="pilih" value="{{ $pilih }}">@endif
            <label>Kategori
                <select name="kategori" onchange="this.form.jenis.value=''; this.form.submit()">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $id => $n)<option value="{{ $id }}" @selected($id == $kategori)>{{ $n }}</option>@endforeach
                </select>
            </label>
            <label>Jenis barang
                <input type="search" name="jenis" value="{{ $jenis }}" list="sl-types" placeholder="mis. AC indoor, Televisi" onchange="this.form.submit()" class="sl-q">
                <datalist id="sl-types">@foreach ($typeOptions as $t)<option value="{{ $t }}">@endforeach</datalist>
            </label>
            @if ($tampilan === 'matriks')
            <label>Baris
                <select name="baris" onchange="this.form.submit()">
                    @foreach (['gedung' => 'Gedung / wilayah', 'lantai' => 'Lantai & area', 'ruang' => 'Kamar / ruang'] as $k => $v)<option value="{{ $k }}" @selected($baris === $k)>{{ $v }}</option>@endforeach
                </select>
            </label>
            @endif
            @if ($filtered)<a class="sl-btn" href="{{ $link(['kategori' => null, 'jenis' => null]) }}">Hapus filter</a>@endif
        </form>
        @if ($filtered)
            <div class="ps-note sl-where">
                <b>{{ $jenis ?: ($categories[$kategori] ?? '') }}</b>: {{ number_format($total['n'], 0, ',', '.') }} unit di {{ collect($nodes)->filter(fn ($n) => $n['own'] > 0)->count() }} lokasi.
                @if ($whereList->isNotEmpty()) Terbanyak:
                    @foreach ($whereList->take(6) as $w)<a href="{{ $link(['pilih' => $w['id'], 'tampilan' => 'pohon']) }}">{{ $w['path'] }}</a> ({{ $w['own'] }})@if (! $loop->last), @endif @endforeach
                @endif
            </div>
        @endif

        @if ($tampilan === 'matriks' && $matrix)
            <section class="sl-tree-card sl-mx-card">
                @if (! $matrix['rows'])
                    <p class="sl-empty">Tidak ada aset yang cocok.</p>
                @else
                <p class="sl-empty">Angka = jumlah unit (termasuk sub-lokasi). Warna makin gelap = makin banyak. Klik nama kolom untuk melihat {{ $matrix['byType'] ? 'jenis' : 'kategori' }} itu ada di mana; klik sel untuk membuka lokasinya di pohon.@if ($matrix['more']) {{ $matrix['more'] }} kolom kecil lain tidak ditampilkan; pilih kategori untuk merinci.@endif</p>
                <div class="sl-mx-wrap"><table class="sl-mx">
                    <thead><tr><th class="sl-mx-corner">Lokasi \ {{ $matrix['byType'] ? 'jenis barang' : 'kategori' }}</th>
                        @foreach ($matrix['cols'] as $c)
                            <th class="sl-mx-col"><a href="{{ $matrix['byType'] ? $link(['jenis' => $c]) : $link(['kategori' => $c, 'jenis' => null]) }}"><span>{{ $matrix['names'][$c] ?? $c }}</span></a></th>
                        @endforeach
                        <th class="sl-mx-col"><span>Jumlah</span></th></tr></thead>
                    <tbody>
                    @foreach ($matrix['rows'] as $r)
                        @php $rowSum = array_sum($matrix['cells'][$r]); @endphp
                        <tr><th class="sl-mx-row"><a href="{{ $link(['pilih' => $r, 'tampilan' => 'pohon']) }}">{{ $nodes[$r]['path'] }}</a></th>
                            @foreach ($matrix['cols'] as $c)
                                @php $v = $matrix['cells'][$r][$c] ?? 0; $a = $v ? round(.12 + .78 * sqrt($v / $matrix['max']), 2) : 0; @endphp
                                <td @if ($v) style="--mx: {{ $a }}" class="{{ $a > .55 ? 'dk' : '' }}" @endif>@if ($v)<a href="{{ $matrix['byType'] ? $link(['pilih' => $r, 'tampilan' => 'pohon', 'jenis' => $c]) : $link(['pilih' => $r, 'tampilan' => 'pohon', 'kategori' => $c]) }}">{{ $v }}</a>@endif</td>
                            @endforeach
                            <td class="sl-mx-sum">{{ number_format($rowSum, 0, ',', '.') }}</td></tr>
                    @endforeach
                    </tbody>
                    <tfoot><tr><th class="sl-mx-row">Jumlah</th>
                        @foreach ($matrix['cols'] as $c)<td class="sl-mx-sum">{{ number_format($matrix['colTotal'][$c], 0, ',', '.') }}</td>@endforeach
                        <td class="sl-mx-sum">{{ number_format(array_sum($matrix['colTotal']), 0, ',', '.') }}</td></tr></tfoot>
                </table></div>
                @endif
            </section>
        @else
        <div class="sl-grid">
            <section class="sl-tree-card">
                <div class="sl-tools">
                    <input type="search" id="sl-q" placeholder="Cari lokasi, mis. 203, Lantai 2, Kitchen…" class="sl-q" autocomplete="off">
                    <button type="button" class="sl-btn" onclick="document.querySelectorAll('.sl-tree details').forEach(d => d.open = true)">Buka semua</button>
                    <button type="button" class="sl-btn" onclick="document.querySelectorAll('.sl-tree details').forEach(d => d.open = false)">Tutup semua</button>
                </div>
                @if ($roots)
                    <ul class="sl-tree">
                        @foreach ($roots as $r)
                            @include('filament.pages.partials.lokasi-node', ['id' => $r, 'depth' => 0])
                        @endforeach
                    </ul>
                @else
                    <p class="sl-empty">Belum ada lokasi. Tambahkan di Pengaturan › Lokasi atau lewat Impor master aset.</p>
                @endif
            </section>

            <aside class="sl-detail">
                @if ($sel)
                    <p class="sl-crumbs">@foreach ($crumbs as $c)<a href="?pilih={{ $c['id'] }}" wire:navigate>{{ $c['name'] }}</a>@if (! $loop->last) › @endif @endforeach</p>
                    <h2 class="sl-dh">{{ $sel['name'] }} <small>{{ $types[$sel['type']] ?? $sel['type'] }}</small></h2>
                    <dl class="sl-facts">
                        <div><dt>Aset (termasuk sub-lokasi)</dt><dd>{{ number_format($sel['n'], 0, ',', '.') }}</dd></div>
                        <div><dt>Langsung di lokasi ini</dt><dd>{{ number_format($sel['own'], 0, ',', '.') }}</dd></div>
                        <div><dt>Berisiko (D/E)</dt><dd class="{{ $sel['risk'] ? 'sl-bad' : '' }}">{{ $sel['risk'] }}</dd></div>
                        <div><dt>Belum dinilai</dt><dd>{{ $sel['unrated'] }}</dd></div>
                        <div><dt>WO terbuka</dt><dd>{{ $sel['wo'] }}</dd></div>
                        @if ($financial)<div><dt>Nilai perolehan</dt><dd>Rp {{ number_format($sel['value'], 0, ',', '.') }}</dd></div>@endif
                        <div><dt>Sub-lokasi</dt><dd>{{ count($sel['children']) }}</dd></div>
                    </dl>
                    <a class="sl-btn sl-btn-p" href="{{ $assetsUrl($sel['id']) }}">Buka di Register aset →</a>
                    @if ($selTypes->isNotEmpty())
                        <h3 class="sl-sub">Jenis barang di lokasi ini <small>(klik untuk melihat ada di mana saja)</small></h3>
                        <div class="sl-types">
                            @foreach ($selTypes as $cat => $types)
                                <div><p class="sl-tcat">{{ $cat }} <small>{{ $types->sum('n') }}</small></p>
                                    <ul>@foreach ($types as $t => $x)<li><a href="{{ $link(['jenis' => $t, 'kategori' => null]) }}">{{ $t }}</a><span>{{ $x['n'] }}@if ($x['risk'])<b class="sl-bad"> · {{ $x['risk'] }} D/E</b>@endif</span></li>@endforeach</ul>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <h3 class="sl-sub">Aset di lokasi ini <small>(berisiko di atas{{ $sel['n'] > 30 ? ', 30 pertama' : '' }})</small></h3>
                    @if ($selAssets->isEmpty())
                        <p class="sl-empty">Belum ada aset tercatat di lokasi ini.</p>
                    @else
                        <ul class="sl-assets">
                            @foreach ($selAssets as $a)
                                <li><a href="{{ $assetUrl($a) }}"><span class="sl-grade g-{{ $a->condition_grade ?? 'x' }}">{{ $a->condition_grade ?? '–' }}</span><span><b>{{ $a->name }}</b><small>{{ $a->asset_code }} · {{ $a->category?->name }}@if ($a->location_id !== $sel['id']) · {{ $a->location?->name }}@endif</small></span></a></li>
                            @endforeach
                        </ul>
                    @endif
                @else
                    <h2 class="sl-dh">Pilih lokasi</h2>
                    <p class="sl-empty">Klik nama gedung, lantai, kamar, atau ruang di pohon untuk melihat ringkasan dan daftar asetnya. Angka di setiap baris sudah termasuk sub-lokasinya.</p>
                    <ul class="sl-legend">
                        <li><span class="sl-grade g-A">A</span>–<span class="sl-grade g-B">B</span> baik / ringan</li>
                        <li><span class="sl-grade g-C">C</span> perlu perbaikan</li>
                        <li><span class="sl-grade g-D">D</span>–<span class="sl-grade g-E">E</span> berisiko: kaji penggantian</li>
                    </ul>
                @endif
            </aside>
        </div>
        @endif
    </div>
    <script>
        (() => {
            const q = document.getElementById('sl-q');
            if (!q || q.dataset.bound) return; q.dataset.bound = 1;
            q.addEventListener('input', () => {
                const v = q.value.trim().toLowerCase();
                const items = document.querySelectorAll('.sl-tree li');
                if (!v) { items.forEach(li => li.hidden = false); return; }
                items.forEach(li => li.hidden = true);
                items.forEach(li => {
                    if (!li.dataset.sl.includes(v)) return;
                    li.hidden = false;
                    li.querySelectorAll('li').forEach(c => c.hidden = false);
                    for (let p = li.parentElement; p && !p.classList.contains('sl-tree'); p = p.parentElement) {
                        if (p.tagName === 'LI') p.hidden = false;
                        if (p.tagName === 'DETAILS') p.open = true;
                    }
                });
            });
        })();
    </script>
</x-filament-panels::page>
