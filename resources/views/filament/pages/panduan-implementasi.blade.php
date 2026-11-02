@php $g = $this->guide; @endphp
<x-filament-panels::page>
<div class="arah ac-help-page">
    <div class="ac-impl ac-noprint">
        <nav class="arah-card ac-impl-toc">
            <h3>Daftar isi</h3>
            <ol>
                @foreach ($this->toc as $t)
                    <li><a href="#{{ \Illuminate\Support\Str::slug($t) }}">{{ preg_replace('/^\d+\.\s*/', '', $t) }}</a></li>
                @endforeach
            </ol>
            <button type="button" class="arah-btn" onclick="window.print()" style="margin-top:.6rem; width:100%; justify-content:center">Cetak / simpan PDF</button>
        </nav>
    </div>
    <article class="arah-card ac-help-body ac-help-full ac-impl-body">
        <h1 class="ac-impl-title">{{ $g['title'] }}</h1>
        {!! $g['html'] !!}
    </article>
</div>
</x-filament-panels::page>
