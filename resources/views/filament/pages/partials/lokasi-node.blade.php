@php
    $n = $nodes[$id];
    $has = count($n['children']) > 0;
    $open = $depth < 1 || in_array($id, $openIds, true);
    $icon = ['company' => '🏛', 'building' => '🏢', 'zone' => '🧭', 'floor' => '▤', 'room' => ['retail' => '🏪', 'general' => '🚪'][\App\Support\Terms::type()] ?? '🛏', 'venue' => '🎤', 'area' => '◫', 'point' => '•'][$n['type']] ?? '•';
    $row = '<span class="sl-ic" aria-hidden="true">'.$icon.'</span>'
        .'<a class="sl-name" href="?pilih='.$id.'" wire:navigate>'.e($n['name']).'</a>'
        .'<span class="sl-type">'.e($types[$n['type']] ?? $n['type']).'</span>'
        .'<span class="sl-stats"><b>'.number_format($n['n'], 0, ',', '.').'</b> aset'
        .($n['risk'] ? ' · <span class="sl-bad">'.$n['risk'].' berisiko</span>' : '')
        .($n['wo'] ? ' · <span class="sl-wo">'.$n['wo'].' WO</span>' : '')
        .($financial && $n['value'] > 0 ? ' · Rp '.number_format($n['value'] / 1e6, 1, ',', '.').' jt' : '')
        .'</span>';
@endphp
<li data-sl="{{ \Illuminate\Support\Str::lower($n['path'].' '.$n['code']) }}" @class(['sl-sel' => $sel && $sel['id'] === $id])>
    @if ($has)
        <details @if ($open) open @endif>
            <summary>{!! $row !!}</summary>
            <ul>
                @foreach ($n['children'] as $c)
                    @include('filament.pages.partials.lokasi-node', ['id' => $c, 'depth' => $depth + 1])
                @endforeach
            </ul>
        </details>
    @else
        <div class="sl-leaf">{!! $row !!}</div>
    @endif
</li>
