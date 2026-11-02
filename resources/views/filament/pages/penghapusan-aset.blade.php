<x-filament-panels::page>
@php $rp = fn ($v) => 'Rp '.number_format((float) $v, 0, ',', '.'); @endphp
<div class="so">
    <div class="sl-kpis">
        <div><b>{{ ($all['submitted']->n ?? 0) }}</b><span>menunggu persetujuan</span></div>
        <div><b>{{ ($all['approved']->n ?? 0) }}</b><span>disetujui, belum dilaksanakan</span></div>
        <div><b>{{ ($all['completed']->n ?? 0) }}</b><span>aset sudah dihapus</span></div>
        @if ($fin)
        <div><b>{{ $rp($all['completed']->bv ?? 0) }}</b><span>nilai buku dihapus</span></div>
        <div><b>{{ $rp(($all['completed']->pr ?? 0) - ($all['completed']->bv ?? 0)) }}</b><span>laba (rugi) pelepasan</span></div>
        @endif
    </div>
    <div class="sl-grid">
        <section class="sl-tree-card">
            <div class="so-tabs">
                @foreach (['aktif' => 'Berjalan', 'selesai' => 'Selesai / ditolak', 'semua' => 'Semua'] as $k => $v)
                    <a href="?status={{ $k }}" @class(['on' => $status === $k])>{{ $v }}</a>
                @endforeach
            </div>
            @if ($rows->isEmpty())
                <p class="sl-empty">Belum ada usulan. Penghapusan diusulkan dari aset rusak berat (kondisi E), aset hilang hasil stock opname, atau aset yang dijual / dihibahkan.</p>
            @endif
            <div class="hp-list">
            @foreach ($rows as $d)
                <article class="hp-card">
                    <header>
                        <div><b>{{ $d->number }}</b> · <a href="{{ \App\Filament\Resources\Assets\AssetResource::getUrl('view', ['record' => $d->asset_id]) }}">{{ $d->asset?->asset_code }}</a> {{ $d->asset?->name }}
                            <small>{{ $d->asset?->location?->full_path ?: $d->asset?->location?->name }}</small></div>
                        <span class="so-st so-{{ $d->status }}">{{ $statuses[$d->status] }}</span>
                    </header>
                    <dl class="sl-facts">
                        <div><dt>Cara</dt><dd>{{ $methods[$d->method] }}</dd></div>
                        <div><dt>Alasan</dt><dd>{{ $d->reason }}</dd></div>
                        @if ($fin)
                        <div><dt>Harga perolehan</dt><dd>{{ $rp($d->acquisition_cost) }}</dd></div>
                        <div><dt>Nilai buku saat usul</dt><dd>{{ $rp($d->book_value) }}</dd></div>
                        @if ($d->proceeds !== null)<div><dt>Hasil penjualan</dt><dd>{{ $rp($d->proceeds) }} ({{ $d->gainLoss() >= 0 ? 'laba' : 'rugi' }} {{ $rp(abs($d->gainLoss())) }})</dd></div>@endif
                        @endif
                        <div><dt>Diusulkan</dt><dd>{{ $d->requester?->name }} · {{ $d->proposed_date?->translatedFormat('d M Y') }}</dd></div>
                        @if ($d->approver)<div><dt>{{ $d->status === 'rejected' ? 'Ditolak' : 'Disetujui' }}</dt><dd>{{ $d->approver->name }} · {{ \App\Support\Fmt::dt($d->approved_at) }}@if ($d->decision_note) · “{{ $d->decision_note }}”@endif</dd></div>@endif
                    </dl>
                    @if ($d->justification)<p class="sl-empty">{{ $d->justification }}</p>@endif
                    <div class="hp-act">
                        @if ($d->status === 'submitted' && $canApprove && ($d->requested_by !== $me || $isAdmin))
                            <input type="text" wire:model="note.{{ $d->id }}" placeholder="Catatan keputusan (wajib bila menolak)">
                            <button type="button" class="sl-btn so-ok" wire:click="decide({{ $d->id }}, true)" wire:confirm="Setujui penghapusan aset ini?">Setujui</button>
                            <button type="button" class="sl-btn so-no" wire:click="decide({{ $d->id }}, false)">Tolak</button>
                        @elseif ($d->status === 'submitted' && $canApprove)
                            <span class="sl-empty">Menunggu penyetuju lain (pemisahan tugas).</span>
                        @endif
                        @if ($d->status === 'approved' && ($canRequest || $canApprove))
                            <input type="date" wire:model="done.{{ $d->id }}.completed_date" title="Tanggal pelaksanaan">
                            @if (in_array($d->method, ['sale', 'trade_in'], true))
                                <input type="number" min="0" wire:model="done.{{ $d->id }}.proceeds" placeholder="Hasil penjualan (Rp)">
                                <input type="text" wire:model="done.{{ $d->id }}.buyer" placeholder="Pembeli">
                            @endif
                            <input type="text" wire:model="done.{{ $d->id }}.document_ref" placeholder="No. berita acara / dokumen">
                            <button type="button" class="sl-btn sl-btn-p" wire:click="complete({{ $d->id }})" wire:confirm="Tandai selesai? Aset akan berstatus dihapus dan keluar dari daftar aset tetap.">Selesaikan</button>
                        @endif
                        @if (in_array($d->status, ['submitted', 'approved'], true) && ($d->requested_by === $me || $canApprove))
                            <button type="button" class="sl-btn" wire:click="cancel({{ $d->id }})" wire:confirm="Batalkan usulan ini?">Batalkan</button>
                        @endif
                        <a class="sl-btn" href="{{ route('disposal.report', $d) }}" target="_blank">Berita acara</a>
                    </div>
                </article>
            @endforeach
            </div>
        </section>
        @if ($canRequest)
        <aside class="sl-detail">
            <h2 class="sl-dh">Usulkan penghapusan</h2>
            <form wire:submit="propose" class="so-form">
                <label>Kode aset <input type="text" wire:model="form.asset_code" placeholder="{{ \App\Support\Branding::code() }}-AST-000123" required>
                    @error('form.asset_code')<small class="sl-bad">{{ $message }}</small>@enderror</label>
                <label>Cara penghapusan
                    <select wire:model="form.method">@foreach ($methods as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></label>
                <label>Alasan singkat <input type="text" wire:model="form.reason" maxlength="250" placeholder="mis. rusak berat, biaya perbaikan > 60% harga baru" required></label>
                <label>Kajian / justifikasi <textarea wire:model="form.justification" rows="3" placeholder="hasil kajian teknis, riwayat perbaikan, rekomendasi"></textarea></label>
                <label>Perkiraan hasil penjualan (Rp) <input type="number" min="0" wire:model="form.proceeds"></label>
                <button type="submit" class="sl-btn sl-btn-p">Ajukan usulan</button>
                <p class="sl-empty">Nilai buku dibekukan saat diusulkan. Penyetuju harus orang lain (pemisahan tugas). Setelah selesai, aset keluar dari daftar aset tetap dan penyusutan.</p>
            </form>
        </aside>
        @endif
    </div>
</div>
</x-filament-panels::page>
