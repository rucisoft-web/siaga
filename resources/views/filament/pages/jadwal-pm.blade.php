<x-filament-panels::page>
<div class="so">
    <div class="sl-kpis">
        <div><b>{{ $kpi['active'] }}</b><span>jadwal aktif</span></div>
        <div><b>{{ $kpi['due7'] }}</b><span>jatuh tempo ≤ 7 hari</span></div>
        <div><b>{{ $kpi['open'] }}</b><span>WO PM terbuka</span></div>
        <div><b @class(['sl-bad' => $kpi['late']])>{{ $kpi['late'] }}</b><span>WO PM lewat jatuh tempo</span></div>
        <div><b>{{ $kpi['compliance'] === null ? '–' : $kpi['compliance'].'%' }}</b><span>kepatuhan PM 90 hari ({{ $kpi['pastN'] }} WO)</span></div>
        <div><b @class(['sl-bad' => $kpi['expiring']])>{{ $kpi['expiring'] }}</b><span>garansi / kontrak berakhir ≤ 30 hari</span></div>
    </div>
    <div @class(['sl-grid' => $canManage && $tab === 'jadwal'])>
        <section class="sl-tree-card">
            <div class="so-tabs">
                @foreach (['jadwal' => 'Jadwal ('.$schedules->count().')', 'wo' => 'WO PM terbuka ('.$open->count().')', 'garansi' => 'Garansi & kontrak ('.$expiring->count().')'] as $k => $v)
                    <a href="?tab={{ $k }}" @class(['on' => $tab === $k])>{{ $v }}</a>
                @endforeach
            </div>

            @if ($tab === 'wo')
                @if ($open->isEmpty())<p class="sl-empty">Tidak ada WO PM terbuka.</p>@else
                <div class="ps-tbl-wrap"><table class="ps-tbl">
                    <thead><tr><th>WO</th><th>Pekerjaan</th><th>Jatuh tempo</th><th>Status</th><th>PIC</th></tr></thead>
                    <tbody>@foreach ($open as $w)
                        <tr><td><a href="{{ $woUrl($w) }}">{{ $w->number }}</a></td>
                            <td>{{ $w->title }}<br><small class="sl-empty">{{ $types[$w->type] ?? $w->type }} · {{ $w->asset ? $w->asset->asset_code.' '.$w->asset->name : ($w->location?->name ?? 'beberapa aset') }}</small></td>
                            <td class="r {{ $w->due_at && $w->due_at->isPast() ? 'sl-bad' : '' }}">{{ $w->pm_due_date?->translatedFormat('d M Y') }}</td>
                            <td>{{ \App\Models\WorkOrder::STATUSES[$w->status] ?? $w->status }}</td><td>{{ $w->pic?->name ?? '–' }}</td></tr>
                    @endforeach</tbody>
                </table></div>@endif
            @elseif ($tab === 'garansi')
                <p class="sl-empty">Garansi aset (tab Perawatan pada aset) dan kontrak vendor (Data referensi › Vendor) yang berakhir dalam 60 hari atau sudah lewat ≤ 30 hari. Pengingat dikirim otomatis H-30 dan H-7.</p>
                @if ($expiring->isEmpty())<p class="sl-empty">Tidak ada yang akan berakhir.</p>@else
                <div class="ps-tbl-wrap"><table class="ps-tbl">
                    <thead><tr><th>Jenis</th><th>Ref</th><th>Nama</th><th>Lokasi / bidang</th><th class="r">Berakhir</th><th class="r">Sisa</th></tr></thead>
                    <tbody>@foreach ($expiring as $r)
                        <tr><td>{{ $r['kind'] }}</td>
                            <td>@if ($r['kind'] === 'Garansi aset')<a href="{{ $assetUrl($r['id']) }}">{{ $r['ref'] }}</a>@else{{ $r['ref'] }}@endif</td>
                            <td>{{ $r['name'] }}</td><td>{{ $r['where'] }}</td>
                            <td class="r">{{ $r['until']->translatedFormat('d M Y') }}</td>
                            <td class="r {{ $r['days'] <= 7 ? 'sl-bad' : '' }}">{{ $r['days'] < 0 ? 'lewat '.abs($r['days']).' hari' : $r['days'].' hari' }}</td></tr>
                    @endforeach</tbody>
                </table></div>@endif
            @else
                @if ($schedules->isEmpty())<p class="sl-empty">Belum ada jadwal. Buat jadwal PM, inspeksi, atau kalibrasi di panel kanan.</p>@endif
                <div class="hp-list">
                @foreach ($schedules as $s)
                    @php $d = (int) $today->diffInDays(\Carbon\CarbonImmutable::parse($s->next_due->toDateString(), \App\Support\Fmt::tz()), false); @endphp
                    <article @class(['hp-card', 'pm-off' => ! $s->is_active])>
                        <header>
                            <div><b>{{ $s->title }}</b> <span class="pm-type pm-{{ $s->type }}">{{ $types[$s->type] }}</span>
                                <small>{{ $s->code }} · {{ $s->targetLabel() }}</small></div>
                            @if (! $s->is_active)<span class="so-st">nonaktif</span>
                            @else<span class="so-st {{ $d < 0 ? 'so-rejected' : ($d <= $s->lead_days ? 'so-submitted' : 'so-closed') }}">{{ $d < 0 ? 'lewat '.abs($d).' hari' : ($d === 0 ? 'hari ini' : $d.' hari lagi') }}</span>@endif
                        </header>
                        <dl class="sl-facts">
                            <div><dt>Interval</dt><dd>{{ $s->intervalLabel() }}</dd></div>
                            <div><dt>Jatuh tempo berikutnya</dt><dd>{{ $s->next_due->translatedFormat('d M Y') }} <small>(WO dibuat H-{{ $s->lead_days }})</small></dd></div>
                            <div><dt>PIC / vendor</dt><dd>{{ $s->pic?->name ?? '–' }}@if ($s->vendor) · {{ $s->vendor->name }}@endif</dd></div>
                            <div><dt>WO terakhir</dt><dd>@if ($s->lastWorkOrder)<a href="{{ $woUrl($s->lastWorkOrder) }}">{{ $s->lastWorkOrder->number }}</a> {{ \App\Models\WorkOrder::STATUSES[$s->lastWorkOrder->status] ?? '' }}@else – @endif</dd></div>
                            @if ($s->regulation)<div><dt>Dasar</dt><dd>{{ $s->regulation }}</dd></div>@endif
                        </dl>
                        @if ($canManage)
                        <div class="hp-act">
                            @if ($s->is_active)<button type="button" class="sl-btn sl-btn-p" wire:click="generateNow({{ $s->id }})" wire:confirm="Buat work order untuk jatuh tempo {{ $s->next_due->translatedFormat('d M Y') }} sekarang?">Buat WO sekarang</button>@endif
                            <button type="button" class="sl-btn" wire:click="edit({{ $s->id }})">Ubah</button>
                            <button type="button" class="sl-btn" wire:click="toggle({{ $s->id }})">{{ $s->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                        </div>
                        @endif
                    </article>
                @endforeach
                </div>
            @endif
        </section>
        @if ($canManage && $tab === 'jadwal')
        <aside class="sl-detail">
            <h2 class="sl-dh">{{ $editing ? 'Ubah jadwal' : 'Jadwal baru' }}</h2>
            <form wire:submit="save" class="so-form">
                <label>Nama pekerjaan <input type="text" wire:model="form.title" maxlength="200" placeholder="mis. Servis AC split bulanan" required></label>
                <label>Jenis <select wire:model="form.type">@foreach ($types as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></label>
                <label>Sasaran <select wire:model.live="form.target"><option value="asset">Satu aset</option><option value="group">Kelompok: kategori di lokasi</option></select></label>
                @if (($form['target'] ?? 'asset') === 'asset')
                    <label>Kode aset <input type="text" wire:model="form.asset_code" placeholder="{{ \App\Support\Branding::code() }}-AST-000123">
                        @error('form.asset_code')<small class="sl-bad">{{ $message }}</small>@enderror</label>
                @else
                    <label>Kategori <select wire:model="form.category_id"><option value="">Semua kategori</option>@foreach ($categories as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select>
                        @error('form.category_id')<small class="sl-bad">{{ $message }}</small>@enderror</label>
                    <label>Lokasi (termasuk sub-lokasi) <select wire:model="form.location_id"><option value="">Semua lokasi</option>@foreach ($locations as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></label>
                @endif
                <label>Interval <select wire:model="form.interval_days">@foreach ($intervals as $k => $v)<option value="{{ $k }}">{{ $v }} ({{ $k }} hari)</option>@endforeach
                    @if (! isset($intervals[(int) ($form['interval_days'] ?? 0)]))<option value="{{ $form['interval_days'] }}">Tiap {{ $form['interval_days'] }} hari</option>@endif</select></label>
                <div class="pm-2">
                    <label>Jatuh tempo berikutnya <input type="date" wire:model="form.next_due" required></label>
                    <label>Buat WO H- <input type="number" min="0" max="60" wire:model="form.lead_days"></label>
                </div>
                <div class="pm-2">
                    <label>Prioritas <select wire:model="form.priority">@foreach (['P2', 'P3', 'P4'] as $p)<option>{{ $p }}</option>@endforeach</select></label>
                    <label>Perkiraan jam <input type="number" step="0.5" min="0" wire:model="form.estimated_hours"></label>
                </div>
                <label>PIC usulan <select wire:model="form.pic_id"><option value="">–</option>@foreach ($pics as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></label>
                <label>Vendor (bila dikerjakan pihak luar) <select wire:model="form.vendor_id"><option value="">–</option>@foreach ($vendors as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></label>
                <label>Dasar kepatuhan <input type="text" wire:model="form.regulation" placeholder="mis. Permenkes 54/2015 kalibrasi alat kesehatan"></label>
                <label>Daftar periksa (satu langkah per baris) <textarea wire:model="form.checklist" rows="4" placeholder="Bersihkan filter&#10;Cek tekanan freon&#10;Ukur arus kompresor"></textarea></label>
                <button type="submit" class="sl-btn sl-btn-p">{{ $editing ? 'Simpan perubahan' : 'Buat jadwal' }}</button>
                @if ($editing)<button type="button" class="sl-btn" wire:click="cancelEdit">Batal</button>@endif
                <p class="sl-empty">Setiap pagi SIAGA membuat work order (draft) untuk jadwal yang jatuh tempo dalam H-n hari, memberi tahu Chief Engineering dan PIC, lalu memajukan tanggal jatuh tempo.</p>
            </form>
        </aside>
        @endif
    </div>
</div>
</x-filament-panels::page>
