@php $profiles = config('assetcare.label_profiles'); $lay = $this->layout; $st = $this->stats; @endphp
<x-filament-panels::page>
<div class="arah">
    <div class="arah-grid arah-grid-3" style="margin-bottom:1rem">
        <div class="arah-card arah-stat"><span class="label">Stok siap pakai</span><span class="value">{{ $st['free'] }}</span><span class="sub">belum terhubung ke aset</span></div>
        <div class="arah-card arah-stat"><span class="label">Sudah terhubung</span><span class="value">{{ $st['bound'] }}</span><span class="sub">ditempel & dipindai saat pendataan</span></div>
        <div class="arah-card arah-stat"><span class="label">Tidak berlaku</span><span class="value">{{ $st['revoked'] }}</span><span class="sub">dirotasi / diganti</span></div>
    </div>
    <div class="arah-grid arah-grid-2">
        <div class="arah-card">
            <h3>Cetak stok baru</h3>
            <p class="arah-muted">Label berisi QR & nomor stok (mis. {{ \App\Support\Branding::code() }}-QR-000001), tanpa nama aset. Petugas menempel label, memindainya di <b>Pendataan aset</b>, lalu memilih aset yang ditempeli. Cocok untuk aset di luar template, area umum, dan printer label mobile.</p>
            <label class="arah-muted">Jumlah label</label>
            <input class="ac-input" type="number" min="1" max="2000" wire:model.live.debounce.400ms="count">
            <label class="arah-muted" style="display:block; margin-top:.5rem">Media</label>
            <select class="ac-input" wire:model.live="profile">
                @foreach ($profiles as $k => $p)<option value="{{ $k }}">{{ $p['name'] }}</option>@endforeach
            </select>
            @if (($profiles[$profile]['type'] ?? '') === 'a4')
                <label class="arah-muted" style="display:block; margin-top:.5rem">Mulai di sel (lembar bekas)</label>
                <input class="ac-input" type="number" min="1" max="{{ $lay['per_sheet'] }}" wire:model.live="startCell">
            @endif
            <p class="arah-muted" style="margin-top:.5rem">{{ $lay['labels'] }} label · {{ $lay['sheets'] }} {{ ($profiles[$profile]['type'] ?? '') === 'a4' ? 'lembar A4' : 'label roll' }}</p>
            <div class="ac-actions">
                <button type="button" class="arah-btn arah-btn-primary" wire:click="create" wire:loading.attr="disabled">Buat stok & PDF</button>
            </div>
            @if ($error)<div class="ac-scan-msg err">{{ $error }}</div>@endif
            @if ($batchId)
                <div class="ac-scan-msg ok">Stok dibuat. <a href="{{ route('labels.pdf', $batchId) }}" target="_blank"><b>Unduh PDF label</b></a> (skala 100%) · <a href="{{ route('labels.print', $batchId) }}" target="_blank"><b>Cetak langsung ke printer portable</b></a></div>
            @endif
        </div>
        <div class="arah-card">
            <h3>Riwayat cetak stok</h3>
            @if ($this->batches->isEmpty())
                <div class="arah-empty">Belum ada stok label.</div>
            @else
                <table class="arah-table">
                    <thead><tr><th>Batch</th><th class="num">Label</th><th>Media</th><th>Dibuat</th><th></th></tr></thead>
                    <tbody>
                    @foreach ($this->batches as $b)
                        <tr><td><b>{{ $b->number }}</b></td><td class="num">{{ $b->label_count }}</td><td>{{ $profiles[$b->media_profile]['name'] ?? $b->media_profile }}</td><td>{{ \App\Support\Fmt::dt($b->generated_at, 'd/m H:i') }}</td>
                            <td><a href="{{ route('labels.pdf', $b) }}" target="_blank">PDF</a></td></tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
</x-filament-panels::page>
