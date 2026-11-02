@php
    $pad = $p['label_h'] >= 40 ? 2.5 : 1.8;
    $qr = min($p['qr_mm'], $p['label_h'] - 2 * $pad);
    $textW = $p['label_w'] - $qr - 3 * $pad;
    $big = $p['label_h'] >= 40;
    $brand = \App\Support\Branding::hotelName();
    $brandColor = \App\Support\Branding::primary();
@endphp
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>{{ $batch->number }}</title>
<style>
    @page { margin: 0; @if (!empty($browser)) size: {{ $p['page_w'] }}mm {{ $p['page_h'] }}mm; @endif }
    @if (!empty($browser))
    body { font-family: Arial, "DejaVu Sans", sans-serif; }
    .code { white-space: nowrap; letter-spacing: -.15pt; font-size: {{ $big ? 9.5 : 6.2 }}pt; }
    .bar { font: 14px/1.4 Arial, sans-serif; padding: 12px 16px; background: #1F2937; color: #fff; display: flex; gap: 10px; align-items: center; flex-wrap: wrap; width: auto; }
    .bar button { font-size: 15px; padding: 8px 14px; border: 0; background: #fff; color: #111; font-weight: bold; }
    @media screen { body { background: #e7e5e4; } .sheet { background: #fff; margin: 12px; outline: 1px solid #a8a29e; zoom: 1.8; } }
    @media print { .bar { display: none; } html, body { width: {{ $p['page_w'] }}mm; } }
    @endif
    * { box-sizing: border-box; }
    body { margin: 0; font-family: "DejaVu Sans", sans-serif; color: #1c1917; }
    .sheet { position: relative; width: {{ $p['page_w'] }}mm; height: {{ $p['page_h'] }}mm; page-break-after: always; overflow: hidden; }
    .sheet:last-child { page-break-after: auto; }
    .cell { position: absolute; width: {{ $p['label_w'] }}mm; height: {{ $p['label_h'] }}mm; overflow: hidden; }
    .guide { outline: 0.2mm dashed #a8a29e; }
    .qr { position: absolute; left: {{ $pad }}mm; top: {{ ($p['label_h'] - $qr) / 2 }}mm; width: {{ $qr }}mm; height: {{ $qr }}mm; }
    .txt { position: absolute; left: {{ $qr + 2 * $pad }}mm; top: {{ $pad }}mm; width: {{ $textW }}mm; height: {{ $p['label_h'] - 2 * $pad }}mm; overflow: hidden; }
    .brand { font-size: {{ $big ? 6.5 : 5 }}pt; color: {{ $brandColor }}; font-weight: bold; letter-spacing: .2pt; text-transform: uppercase; }
    .code { font-size: {{ $big ? 9.5 : 7 }}pt; font-weight: bold; margin-top: .6mm; }
    .name { font-size: {{ $big ? 7.5 : 5.6 }}pt; margin-top: .6mm; line-height: 1.15; max-height: {{ $big ? 9 : 5.2 }}mm; overflow: hidden; }
    .loc { font-size: {{ $big ? 7 : 5.4 }}pt; margin-top: .6mm; font-weight: bold; color: #44403c; }
    .foot { position: absolute; bottom: 0; left: 0; font-size: {{ $big ? 5.5 : 4.2 }}pt; color: #57534e; }
    .copy { position: absolute; right: .8mm; bottom: .4mm; font-size: 3.6pt; color: #a8a29e; }
</style>
</head>
<body>
@if (!empty($browser))
    <div class="bar">
        <button type="button" onclick="window.print()">Cetak {{ $batch->label_count }} label</button>
        <span>{{ $batch->number }} · {{ $p['name'] }}. Pilih printer label Bluetooth, skala 100%, ukuran kertas {{ $p['page_w'] }} × {{ $p['page_h'] }} mm.</span>
        <button type="button" onclick="history.length > 1 ? history.back() : window.close()">Kembali</button>
    </div>
@endif
@foreach ($sheets as $sheetNo => $cells)
    <div class="sheet">
        @foreach ($cells as $c)
            @php
                $i = $c['item'];
                $idx = $i->cell_no - 1;
                $col = $idx % $p['cols'];
                $row = intdiv($idx, $p['cols']);
                $left = $p['margin_left'] + $col * ($p['label_w'] + $p['gap_x']);
                $top = $p['margin_top'] + $row * ($p['label_h'] + $p['gap_y']);
            @endphp
            <div class="cell {{ $guides ? 'guide' : '' }}" style="left: {{ $left }}mm; top: {{ $top }}mm;">
                <img class="qr" src="{{ $c['qr'] }}" alt="">
                <div class="txt">
                    <div class="brand">{{ $brand }}</div>
                    <div class="code">{{ $i->code_snapshot }}</div>
                    <div class="name">{{ \Illuminate\Support\Str::limit($i->name_snapshot, $big ? 60 : 38) }}</div>
                    @if ($i->location_snapshot)<div class="loc">{{ \Illuminate\Support\Str::limit($i->location_snapshot, 28) }}</div>@endif
                    <div class="foot">Scan untuk info &amp; lapor kerusakan</div>
                </div>
                <div class="copy">{{ $batch->number }}·{{ $i->sequence }}</div>
            </div>
        @endforeach
    </div>
@endforeach
@if (!empty($browser) && !empty($auto))
<script>window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 400); });</script>
@endif
</body>
</html>
