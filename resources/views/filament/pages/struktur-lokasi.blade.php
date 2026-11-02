<x-filament-panels::page>
    @php $openIds = collect($crumbs)->pluck('id')->all(); @endphp
    <div class="sl-wrap">
        <div class="sl-kpis">
            <div><b>{{ number_format($total['loc'], 0, ',', '.') }}</b><span>lokasi</span></div>
            <div><b>{{ number_format($total['n'], 0, ',', '.') }}</b><span>aset berlokasi</span></div>
            <div><b class="sl-bad">{{ number_format($total['risk'], 0, ',', '.') }}</b><span>aset berisiko (D/E)</span></div>
            <div><b>{{ number_format($total['wo'], 0, ',', '.') }}</b><span>work order terbuka</span></div>
        </div>

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
