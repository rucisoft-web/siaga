@php
    use App\Models\Asset;
    $stateText = ['todo' => 'Belum', 'labeled' => 'Label OK · cek kondisi', 'checked' => 'Kondisi OK · pindai label', 'done' => 'Selesai', 'missing' => 'Tidak ada'];
    $stateCls = ['todo' => 'arah-abu', 'labeled' => 'arah-kuning', 'checked' => 'arah-kuning', 'done' => 'arah-hijau', 'missing' => 'arah-merah'];
@endphp
<x-filament-panels::page>
<div class="arah sv">
    <div class="sv-tabs">
        @if ($this->canRun)
            <button type="button" class="{{ $tab === 'kamar' ? 'on' : '' }}" wire:click="$set('tab','kamar')">Kamar & ruang</button>
        @endif
        <button type="button" class="{{ $tab === 'progres' ? 'on' : '' }}" wire:click="$set('tab','progres')">Progres</button>
    </div>

    {{-- ===================== PROGRES ===================== --}}
    @if ($tab === 'progres')
        @php $pg = $this->progress; @endphp
        <div class="arah-card">
            <h3>Progres per lantai / zona</h3>
            @if ($pg['floors']->isEmpty())
                <div class="arah-empty">Belum ada kamar berisi aset. Generate aset kamar dari template lebih dulu.</div>
            @else
                <div class="arah-scroll"><table class="arah-table">
                    <thead><tr><th>Lantai / zona</th><th class="num">Ruang</th><th class="num">Selesai</th><th class="num">Proses</th><th class="num">Item</th><th class="num">Ada</th><th class="num">Tidak ada</th><th style="min-width:120px">Progres</th></tr></thead>
                    <tbody>
                    @foreach ($pg['floors'] as $f)
                        @php $pct = $f['rooms'] ? round($f['done'] / $f['rooms'] * 100) : 0; @endphp
                        <tr><td><b>{{ $f['floor'] }}</b></td><td class="num">{{ $f['rooms'] }}</td><td class="num">{{ $f['done'] }}</td><td class="num">{{ $f['in_progress'] }}</td><td class="num">{{ $f['items'] }}</td><td class="num">{{ $f['found'] }}</td><td class="num">{{ $f['missing'] }}</td>
                            <td><div class="arah-bar"><span style="width:{{ $pct }}%; background:var(--p)"></span></div><span class="arah-muted">{{ $pct }}%</span></td></tr>
                    @endforeach
                    </tbody>
                </table></div>
            @endif
        </div>
        <div class="arah-card" style="margin-top:1rem">
            <h3>Per petugas</h3>
            @if ($pg['people']->isEmpty())
                <div class="arah-empty">Belum ada kamar yang selesai didata.</div>
            @else
                <div class="arah-scroll"><table class="arah-table">
                    <thead><tr><th>Petugas</th><th class="num">Kamar selesai</th><th class="num">Hari ini</th><th class="num">Tidak ada</th><th class="num">Tambahan</th><th>Terakhir</th></tr></thead>
                    <tbody>
                    @foreach ($pg['people'] as $p)
                        <tr><td><b>{{ $p['name'] }}</b></td><td class="num">{{ $p['rooms'] }}</td><td class="num">{{ $p['today'] }}</td><td class="num">{{ $p['missing'] }}</td><td class="num">{{ $p['extra'] }}</td><td>{{ \App\Support\Fmt::dt($p['last'], 'd/m H:i') }}</td></tr>
                    @endforeach
                    </tbody>
                </table></div>
            @endif
        </div>

    {{-- ===================== DAFTAR KAMAR ===================== --}}
    @elseif (! $this->room)
        <div class="arah-card">
            <h3>Pilih kamar / ruang</h3>
            <div class="sv-filters">
                <input class="ac-input" type="search" inputmode="numeric" placeholder="Nomor kamar / nama ruang" wire:model.live.debounce.300ms="search">
                <select class="ac-input" wire:model.live="floorId">
                    <option value="">Semua lantai / zona</option>
                    @foreach ($this->floors as $f)<option value="{{ $f->id }}">{{ $f->full_path ?: $f->name }}</option>@endforeach
                </select>
                <select class="ac-input" wire:model.live="filter">
                    <option value="belum">Belum selesai</option>
                    <option value="selesai">Sudah selesai</option>
                    <option value="semua">Semua</option>
                </select>
            </div>
            <div class="sv-rooms">
                @forelse ($this->roomList as $x)
                    @php $st = $x->survey?->status; @endphp
                    <button type="button" class="sv-room {{ $st === 'done' ? 'done' : ($st ? 'prog' : '') }}" wire:click="openRoom({{ $x->room->id }})" wire:key="r-{{ $x->room->id }}">
                        <span class="no">{{ $x->room->room_ref ?: $x->room->code }}</span>
                        <span class="nm">{{ $x->room->location_type === 'room' ? ($x->room->roomType?->name ?? 'Kamar') : $x->room->name }}</span>
                        <span class="ct">{{ $x->checked }}/{{ $x->total }} item</span>
                        <span class="st">{{ $st === 'done' ? 'Selesai' : ($st ? 'Proses' : ($x->total ? 'Belum' : 'Kosong')) }}</span>
                    </button>
                @empty
                    <div class="arah-empty">Tidak ada ruang yang cocok.</div>
                @endforelse
            </div>
        </div>

    {{-- ===================== KAMAR TERPILIH ===================== --}}
    @else
        @php
            $items = $this->items;
            $states = $items->mapWithKeys(fn ($a) => [$a->id => $this->state($a)]);
            $doneN = $states->filter(fn ($s) => in_array($s, ['done', 'missing', 'checked']))->count();
            $survey = $this->survey;
            $blank = $this->blankLabel;
        @endphp
        <div class="sv-head">
            <button type="button" class="arah-btn" wire:click="backToList">‹ Daftar</button>
            <div class="sv-title">
                <b>{{ $this->room->labelText() }}</b>
                <span class="arah-muted">{{ $this->room->roomType?->name ?? $this->room->full_path }}</span>
            </div>
            <div class="sv-count"><b>{{ $doneN }}</b>/{{ $items->count() }}</div>
        </div>
        <div class="arah-bar" style="margin:.4rem 0 1rem"><span style="width:{{ $items->count() ? round($doneN / $items->count() * 100) : 0 }}%; background:var(--p)"></span></div>

        @if ($survey?->status === 'done')
            <div class="ac-scan-msg ok" style="margin-bottom:1rem">
                Kamar ini sudah selesai didata {{ \App\Support\Fmt::dt($survey->finished_at, 'd/m H:i') }} oleh {{ $survey->surveyor?->name }}
                ({{ $survey->found_count }} ada · {{ $survey->missing_count }} tidak ada · {{ $survey->extra_count }} tambahan).
                <div class="ac-actions">
                    @if ($this->nextRoom)<button type="button" class="arah-btn arah-btn-primary" wire:click="goNext">Kamar berikutnya: {{ $this->nextRoom->labelText() }} ›</button>@endif
                    <button type="button" class="arah-btn" wire:click="reopenRoom">Buka lagi</button>
                </div>
            </div>
        @endif

        <div class="arah-card sv-print">
            <h3>1 · Cetak label kamar ini</h3>
            <div class="sv-print-row">
                <select class="ac-input" wire:model.live="printProfile" aria-label="Ukuran label">
                    @foreach ($this->printProfiles as $k => $n)<option value="{{ $k }}">{{ $n }}</option>@endforeach
                </select>
                <button type="button" class="arah-btn arah-btn-primary" wire:click="printRoom" @disabled(! $this->unprinted)>
                    {{ $this->unprinted ? 'Siapkan '.$this->unprinted.' label belum dicetak' : 'Semua label sudah dicetak' }}
                </button>
            </div>
            @if ($printUrl)
                <a class="arah-btn arah-btn-primary sv-print-go" href="{{ $printUrl }}" target="_blank" rel="noopener">Buka &amp; cetak {{ $printCount }} label ›</a>
            @endif
            <p class="arah-muted">Urutan label = urutan item di bawah. Label hilang/rusak: buka item lalu ketuk "Cetak ulang label".</p>
        </div>

        <div class="arah-card sv-scan" style="margin-top:1rem">
            <h3>2 · Tempel label, lalu pindai</h3>
            <x-ac.scanner method="scan" hint="Pindai label yang baru ditempel" :autofocus="true" />
            @foreach ($feed as $f)
                <div class="ac-scan-msg {{ in_array($f['level'], ['danger', 'warning']) ? 'err' : 'ok' }}" wire:key="f-{{ $loop->index }}-{{ $f['at'] }}"><span class="arah-muted">{{ $f['at'] }}</span> {{ $f['message'] }}</div>
            @endforeach
            @if ($blank)
                <div class="sv-blank">
                    <b>Label stok {{ $blank->stock_code }}</b> belum terhubung. Ketuk <b>"Pakai label ini"</b> pada aset yang ditempeli, atau buat <b>aset tambahan</b>.
                    <button type="button" class="arah-btn" wire:click="cancelBlank">Batal</button>
                </div>
            @endif
        </div>

        <div class="arah-card" style="margin-top:1rem">
            <h3>3 · Isi kamar ({{ $items->count() }} item)</h3>
            @if ($items->isEmpty())
                <div class="arah-empty">Belum ada aset tercatat di ruang ini. Tambahkan lewat <b>Aset tambahan</b> di bawah, atau generate dari template tipe kamar.</div>
            @endif
            <div class="sv-items">
                @foreach ($items as $a)
                    @php $s = $states[$a->id]; $open = $focusId === $a->id; @endphp
                    <div class="sv-item s-{{ $s }} {{ $open ? 'open' : '' }}" wire:key="it-{{ $a->id }}">
                        <button type="button" class="sv-item-h" wire:click="focus({{ $a->id }})">
                            <span class="nm">{{ $a->name }}</span>
                            <span class="cd">{{ $a->asset_code }}@if($a->condition_grade && $s !== 'missing') · {{ $a->condition_grade }}@endif</span>
                            <span class="arah-badge {{ $stateCls[$s] }}">{{ $stateText[$s] }}</span>
                        </button>
                        @if ($blank && ! in_array($s, ['done', 'labeled', 'missing']))
                            <button type="button" class="arah-btn arah-btn-primary sv-use" wire:click="useBlankFor({{ $a->id }})">Pakai label {{ $blank->stock_code }} di sini</button>
                        @endif
                        @if ($open)
                            <div class="sv-form">
                                <label class="arah-muted">Kondisi</label>
                                <div class="sv-cond">
                                    @foreach (Asset::CONDITIONS as $k => $t)
                                        <button type="button" class="{{ $cond === $k ? 'on' : '' }}" wire:click="$set('cond','{{ $k }}')" title="{{ $t }}"><b>{{ $k }}</b><small>{{ \Illuminate\Support\Str::after($t, '— ') }}</small></button>
                                    @endforeach
                                </div>
                                <details class="sv-more" @if($brand || $serial) open @endif>
                                    <summary>Merek, model, serial, foto (opsional)</summary>
                                    <div class="sv-grid">
                                        <input class="ac-input" placeholder="Merek" wire:model="brand">
                                        <input class="ac-input" placeholder="Model / tipe" wire:model="model">
                                        <input class="ac-input" placeholder="Nomor seri" wire:model="serial">
                                        <label class="arah-btn" style="justify-content:center; cursor:pointer">Foto aset / nameplate
                                            <input type="file" accept="image/*" capture="environment" wire:model="photo" style="display:none">
                                        </label>
                                    </div>
                                    <div wire:loading wire:target="photo" class="arah-muted">Mengunggah foto…</div>
                                    @if ($photo)<div class="arah-muted">Foto siap disimpan.</div>@endif
                                </details>
                                <div class="ac-actions">
                                    <button type="button" class="arah-btn arah-btn-primary" wire:click="saveItem" @disabled(! $cond)>Simpan · Ada</button>
                                    <button type="button" class="arah-btn" wire:click="markMissing({{ $a->id }})" wire:confirm="Tandai {{ $a->name }} TIDAK ADA di kamar ini?">Tidak ada</button>
                                    <button type="button" class="arah-btn" wire:click="printRoom({{ $a->id }})">Cetak ulang label</button>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            @if ($this->groups->isNotEmpty())
                <h3 style="margin-top:1rem">Item hitungan (tanpa label per unit)</h3>
                <div class="sv-items">
                    @foreach ($this->groups as $g)
                        <div class="sv-item sv-count-row {{ $g->on_hand !== null ? 's-done' : 's-todo' }}" wire:key="g-{{ $g->id }}">
                            <span class="nm">{{ \Illuminate\Support\Str::before($g->name, ' · ') }}</span>
                            <span class="cd">standar {{ $g->expected_qty !== null ? (float) $g->expected_qty : '-' }} {{ $g->unit }}</span>
                            <input class="ac-input" type="number" inputmode="decimal" min="0" step="any" wire:model="counts.{{ $g->id }}" aria-label="Jumlah fisik {{ $g->name }}">
                            <button type="button" class="arah-btn" wire:click="saveCount({{ $g->id }})">Simpan</button>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="sv-extra">
                @if (! $showExtra)
                    <button type="button" class="arah-btn" wire:click="$set('showExtra', true)">+ Aset tambahan (di luar template)</button>
                @else
                    <h3 style="margin-top:.4rem">Aset tambahan {{ $blank ? '· label '.$blank->stock_code : '' }}</h3>
                    <div class="sv-grid">
                        <select class="ac-input" wire:model="extra.category_id">
                            <option value="">Kategori…</option>
                            @foreach ($this->categories as $id => $n)<option value="{{ $id }}">{{ $n }}</option>@endforeach
                        </select>
                        <input class="ac-input" placeholder="Nama aset, mis. Kursi kerja" wire:model="extra.name">
                        <input class="ac-input" placeholder="Merek (opsional)" wire:model="extra.brand">
                        <input class="ac-input" placeholder="Nomor seri (opsional)" wire:model="extra.serial_number">
                    </div>
                    <div class="sv-cond" style="margin-top:.5rem">
                        @foreach (Asset::CONDITIONS as $k => $t)
                            <button type="button" class="{{ ($extra['condition_grade'] ?? '') === $k ? 'on' : '' }}" wire:click="$set('extra.condition_grade','{{ $k }}')"><b>{{ $k }}</b><small>{{ \Illuminate\Support\Str::after($t, '— ') }}</small></button>
                        @endforeach
                    </div>
                    <label class="arah-btn" style="margin-top:.5rem; cursor:pointer">Foto (opsional)<input type="file" accept="image/*" capture="environment" wire:model="extraPhoto" style="display:none"></label>
                    @error('extra.name')<div class="ac-scan-msg err">Nama aset wajib diisi.</div>@enderror
                    @error('extra.category_id')<div class="ac-scan-msg err">Kategori wajib dipilih.</div>@enderror
                    <div class="ac-actions">
                        <button type="button" class="arah-btn arah-btn-primary" wire:click="addExtra">Simpan aset tambahan</button>
                        <button type="button" class="arah-btn" wire:click="$set('showExtra', false)">Batal</button>
                    </div>
                    @unless ($blank)<p class="arah-muted">Tanpa label stok, label aset ini dicetak belakangan. Untuk langsung berlabel: pindai label stok kosong dulu.</p>@endunless
                @endif
            </div>
        </div>

        @if ($survey?->status !== 'done')
            <div class="sv-finish">
                <button type="button" class="arah-btn arah-btn-primary" wire:click="finishRoom">4 · Selesai kamar ini</button>
                @if ($this->nextRoom)<button type="button" class="arah-btn" wire:click="goNext">Lewati › {{ $this->nextRoom->labelText() }}</button>@endif
            </div>
        @endif
    @endif
</div>
</x-filament-panels::page>
