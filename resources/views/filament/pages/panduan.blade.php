<x-filament-panels::page>
<div class="arah ac-help-page">
    @if ($baca)
        @php $h = \App\Support\Help::get($baca); @endphp
        <div class="ac-actions ac-noprint" style="margin-bottom:1rem">
            <a class="arah-btn" href="{{ \App\Filament\Pages\Panduan::getUrl() }}">‹ Semua panduan</a>
            <button type="button" class="arah-btn" onclick="window.print()">Cetak / simpan PDF</button>
        </div>
        <article class="arah-card ac-help-body ac-help-full">{!! $h['html'] !!}</article>
    @else
        <p class="arah-muted">Setiap layar punya tombol <b>? Panduan</b> di pojok kanan atas. Halaman ini mengumpulkan semuanya. Anda hanya melihat menu sesuai hak akses, tetapi semua panduan bisa dibaca di sini.</p>
        @foreach ($this->index as $group => $items)
            <div class="arah-card" style="margin-top:1rem">
                <h3>{{ $group }}</h3>
                <div class="ac-help-list">
                    @foreach ($items as $k => $it)
                        <a id="{{ $k }}" href="{{ \App\Filament\Pages\Panduan::getUrl(['baca' => $k]) }}" class="ac-help-item">
                            <b>{{ $it['title'] }}</b>
                            <span>{{ \Illuminate\Support\Str::limit($it['summary'], 150) }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif
</div>
</x-filament-panels::page>
