<x-filament-panels::page>
@php $q = fn (array $p) => '?'.http_build_query(array_filter($p + ['sesi' => $take?->id, 'tab' => $tab, 'ruang' => $ruang], fn ($v) => $v !== null && $v !== '')); @endphp
<div class="so">
@if (! $take)
    {{-- daftar sesi + buat sesi --}}
    <div class="sl-grid">
        <section class="sl-tree-card">
            <h2 class="sl-dh">Sesi stock opname</h2>
            @if ($sessions->isEmpty())
                <p class="sl-empty">Belum ada sesi. Stock opname dilakukan minimal setahun sekali (atau per semester) untuk mencocokkan register dengan fisik aset.</p>
            @else
            <div class="ps-tbl-wrap"><table class="ps-tbl">
                <thead><tr><th>Nomor</th><th>Judul</th><th>Cakupan</th><th>Cut-off</th><th class="r">Progres</th><th class="r">Tidak ditemukan</th><th>Status</th></tr></thead>
                <tbody>
                @foreach ($sessions as $s)
                    @php $p = $progress[$s->id] ?? null; @endphp
                    <tr><td><a href="?sesi={{ $s->id }}">{{ $s->number }}</a></td><td>{{ $s->title }}</td><td>{{ $s->location?->full_path ?: ($s->location?->name ?? 'Semua lokasi') }}</td>
                        <td>{{ $s->cutoff_date?->translatedFormat('d M Y') }}</td>
                        <td class="r">{{ $p ? $p->done.'/'.$p->n : '0' }}</td><td class="r">{{ $p?->nf ?? 0 }}</td>
                        <td><span class="so-st so-{{ $s->status }}">{{ \App\Models\StockTake::STATUSES[$s->status] ?? $s->status }}</span></td></tr>
                @endforeach
                </tbody>
            </table></div>
            @endif
        </section>
        @if ($canManage)
        <aside class="sl-detail">
            <h2 class="sl-dh">Buka sesi baru</h2>
            <form wire:submit="create" class="so-form">
                <label>Judul <input type="text" wire:model="form.title" required maxlength="150"></label>
                <label>Cakupan lokasi
                    <select wire:model="form.location_id">
                        <option value="">Semua lokasi</option>
                        @foreach ($locations as $id => $n)<option value="{{ $id }}">{{ $n }}</option>@endforeach
                    </select>
                </label>
                <label>Tanggal cut-off <input type="date" wire:model="form.cutoff_date" required></label>
                <button type="submit" class="sl-btn sl-btn-p">Buka sesi &amp; salin daftar aset</button>
                <p class="sl-empty">Daftar aset dalam cakupan disalin sebagai acuan. Aset yang dipindai di luar cakupan tetap dicatat sebagai temuan pindah lokasi.</p>
            </form>
        </aside>
        @endif
    </div>
@else
    <p class="sl-crumbs"><a href="?">← Semua sesi</a></p>
    <div class="so-head">
        <div>
            <h2 class="sl-dh">{{ $take->number }} · {{ $take->title }} <small>{{ \App\Models\StockTake::STATUSES[$take->status] }}</small></h2>
            <p class="sl-empty">Cakupan: {{ $take->location?->full_path ?: ($take->location?->name ?? 'semua lokasi') }} · cut-off {{ $take->cutoff_date?->translatedFormat('d M Y') }}
                @if ($take->closed_at) · ditutup {{ \App\Support\Fmt::dt($take->closed_at) }} oleh {{ $take->closer?->name }}{{ $take->applied ? ' (hasil diterapkan ke register)' : '' }} @endif</p>
        </div>
        <a class="sl-btn" href="{{ route('stocktake.report', $take) }}" target="_blank">Berita acara</a>
    </div>
    <div class="so-bar"><span style="width: {{ $sum['pct'] }}%"></span></div>
    <div class="sl-kpis">
        <div><b>{{ $sum['done'] }}/{{ $sum['total'] }}</b><span>aset dihitung ({{ $sum['pct'] }}%)</span></div>
        <div><b>{{ $sum['counts']['found'] }}</b><span>ditemukan di tempat</span></div>
        <div><b class="so-warn">{{ $sum['counts']['moved'] }}</b><span>ditemukan di lokasi lain</span></div>
        <div><b class="sl-bad">{{ $sum['counts']['not_found'] }}</b><span>tidak ditemukan</span></div>
        <div><b>{{ $sum['counts']['unregistered'] }}</b><span>temuan tak terdaftar</span></div>
        <div><b>{{ $sum['accuracy'] !== null ? $sum['accuracy'].'%' : '—' }}</b><span>akurasi register</span></div>
    </div>
    <div class="so-tabs">
        @if ($take->status === 'counting' && $canCount)<a href="{{ $q(['tab' => 'hitung']) }}" @class(['on' => $tab === 'hitung'])>Hitung per ruang</a>@endif
        <a href="{{ $q(['tab' => 'hasil']) }}" @class(['on' => $tab === 'hasil' || $take->status !== 'counting' || ! $canCount])>Hasil &amp; selisih</a>
    </div>

    @if ($tab === 'hitung' && $take->status === 'counting' && $canCount)
        <div class="sl-grid">
            <section class="sl-tree-card">
                <label class="so-lbl">Ruang / lokasi yang sedang dihitung
                    <select onchange="window.location.search = '{{ $q(['ruang' => '__R__']) }}'.replace('__R__', this.value).slice(1)">
                        <option value="">Pilih ruang…</option>
                        @foreach ($locs as $l)
                            @php $r = $rooms[$l->id]; @endphp
                            <option value="{{ $l->id }}" @selected($room?->id === $l->id)>{{ $l->full_path ?: $l->name }} · {{ $r->done }}/{{ $r->n }}{{ $r->done == $r->n ? ' ✓' : '' }}</option>
                        @endforeach
                    </select>
                </label>
                @if ($room)
                    <div class="so-scan">
                        <x-ac.scanner method="scan" hint="Pindai label QR aset di ruang ini" />
                        <label class="so-lbl so-inline">Kondisi yang dicatat saat pindai
                            <select wire:model="grade"><option value="">Tidak diubah</option>@foreach ($grades as $g => $n)<option value="{{ $g }}">{{ $n }}</option>@endforeach</select>
                        </label>
                        @if ($message)<div class="so-msg so-{{ $level }}">{{ $message }}</div>@endif
                    </div>
                    <h3 class="sl-sub">Aset di {{ $room->name }}</h3>
                    <ul class="so-items">
                        @foreach ($here as $it)
                            <li class="so-r-{{ $it->result }}">
                                <span class="sl-grade g-{{ $it->found_grade ?? $it->asset?->condition_grade ?? 'x' }}">{{ $it->found_grade ?? $it->asset?->condition_grade ?? '–' }}</span>
                                <span class="so-name"><b>{{ $it->asset?->name ?? $it->description }}</b><small>{{ $it->asset?->asset_code ?? 'tidak terdaftar' }} · {{ $results[$it->result] }}@if ($it->result === 'moved' && $it->expected_location_id === $room->id) → {{ $it->foundLocation?->name }}@elseif ($it->result === 'moved') (seharusnya di lokasi lain)@endif</small></span>
                                <span class="so-act">
                                    @if ($it->result === 'pending')
                                        <button type="button" class="sl-btn so-ok" wire:click="found({{ $it->id }})">Ada</button>
                                        <button type="button" class="sl-btn so-no" wire:click="missing({{ $it->id }})">Tidak ada</button>
                                    @else
                                        <button type="button" class="sl-btn" wire:click="undo({{ $it->id }})">Batalkan</button>
                                    @endif
                                </span>
                            </li>
                        @endforeach
                    </ul>
                    <button type="button" class="sl-btn so-no" wire:click="remainingMissing" wire:confirm="Tandai semua aset yang belum dihitung di ruang ini sebagai TIDAK DITEMUKAN?">Tandai sisa: tidak ditemukan</button>
                @else
                    <p class="sl-empty">Pilih ruang dulu. Petugas mendatangi ruang, memindai label setiap aset (atau ketuk <b>Ada</b>), lalu menandai yang tidak ada.</p>
                @endif
            </section>
            <aside class="sl-detail">
                <h3 class="sl-sub" style="margin-top:0">Temuan aset tak terdaftar</h3>
                <p class="sl-empty">Barang fisik ada tetapi tidak ada di register / tidak berlabel.</p>
                <form wire:submit="addNew" class="so-form">
                    <label>Uraian barang <input type="text" wire:model="baru.description" maxlength="250" placeholder="mis. Kursi lipat hitam 4 unit" required></label>
                    <label>Catatan <input type="text" wire:model="baru.note" maxlength="250" placeholder="merek, nomor seri, kondisi"></label>
                    <button type="submit" class="sl-btn" @disabled(! $room)>Catat di {{ $room?->name ?? 'ruang terpilih' }}</button>
                </form>
                @if ($canManage)
                    <h3 class="sl-sub">Tutup sesi</h3>
                    <label class="so-chk"><input type="checkbox" wire:model="apply"> Terapkan hasil ke register: perbarui lokasi aset yang pindah dan kondisi yang berubah, tandai terverifikasi.</label>
                    <button type="button" class="sl-btn sl-btn-p" wire:click="close" wire:confirm="Tutup sesi? Aset yang belum dihitung akan dicatat TIDAK DITEMUKAN dan sesi tidak bisa dibuka lagi.">Tutup sesi stock opname</button>
                @endif
            </aside>
        </div>
    @else
        @php $disc ??= null; @endphp
        @if ($disc)
            <h2 class="dp-h">Tidak ditemukan ({{ $disc['not_found']->count() }})</h2>
            @if ($disc['not_found']->isEmpty())<p class="sl-empty">Tidak ada.</p>@else
            <div class="ps-tbl-wrap"><table class="ps-tbl"><thead><tr><th>Kode</th><th>Aset</th><th>Lokasi tercatat</th><th>Tindak lanjut</th></tr></thead><tbody>
            @foreach ($disc['not_found'] as $it)
                <tr><td>{{ $it->asset?->asset_code }}</td><td>{{ $it->asset?->name }}</td><td>{{ $it->expectedLocation?->full_path ?: $it->expectedLocation?->name }}</td>
                    <td>@if (isset($disc['proposed'][$it->asset_id]))<span class="so-st so-closed">Diusulkan dihapus</span>@elseif ($canDispose && $take->status === 'closed')<button type="button" class="sl-btn" wire:click="proposeDisposal({{ $it->id }})">Usulkan penghapusan (hilang)</button>@else <span class="sl-empty">Cari ulang / usulkan penghapusan setelah sesi ditutup</span>@endif</td></tr>
            @endforeach
            </tbody></table></div>@endif

            <h2 class="dp-h">Ditemukan di lokasi lain ({{ $disc['moved']->count() }})</h2>
            @if ($disc['moved']->isEmpty())<p class="sl-empty">Tidak ada.</p>@else
            <div class="ps-tbl-wrap"><table class="ps-tbl"><thead><tr><th>Kode</th><th>Aset</th><th>Lokasi tercatat</th><th>Lokasi ditemukan</th></tr></thead><tbody>
            @foreach ($disc['moved'] as $it)
                <tr><td>{{ $it->asset?->asset_code }}</td><td>{{ $it->asset?->name }}</td><td>{{ $it->expectedLocation?->full_path ?: '—' }}</td><td><b>{{ $it->foundLocation?->full_path ?: $it->foundLocation?->name }}</b></td></tr>
            @endforeach
            </tbody></table></div>@endif

            <h2 class="dp-h">Kondisi berubah ({{ $disc['grade']->count() }})</h2>
            @if ($disc['grade']->isEmpty())<p class="sl-empty">Tidak ada.</p>@else
            <div class="ps-tbl-wrap"><table class="ps-tbl"><thead><tr><th>Kode</th><th>Aset</th><th>Kondisi tercatat</th><th>Kondisi saat opname</th></tr></thead><tbody>
            @foreach ($disc['grade'] as $it)
                <tr><td>{{ $it->asset?->asset_code }}</td><td>{{ $it->asset?->name }}</td><td>{{ $it->expected_grade ?? '—' }}</td><td><b>{{ $it->found_grade }}</b></td></tr>
            @endforeach
            </tbody></table></div>@endif

            <h2 class="dp-h">Temuan tak terdaftar ({{ $disc['unregistered']->count() }})</h2>
            @if ($disc['unregistered']->isEmpty())<p class="sl-empty">Tidak ada.</p>@else
            <div class="ps-tbl-wrap"><table class="ps-tbl"><thead><tr><th>Uraian</th><th>Lokasi</th><th>Catatan</th><th>Dicatat oleh</th></tr></thead><tbody>
            @foreach ($disc['unregistered'] as $it)
                <tr><td>{{ $it->description }}</td><td>{{ $it->foundLocation?->full_path ?: '—' }}</td><td>{{ $it->note }}</td><td>{{ $it->counter?->name }}</td></tr>
            @endforeach
            </tbody></table></div>@endif
        @endif
    @endif
@endif
</div>
</x-filament-panels::page>
