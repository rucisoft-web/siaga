@extends('landing.layout')
@section('title', ($g['title'] ?? 'Panduan penggunaan').' — SIAGA')
@section('content')
<section class="ln-sec">
    <div class="ln-wrap ln-doc">
        <aside class="ln-doc-nav">
            <a href="{{ route('landing.guides') }}" class="ln-doc-all">Semua panduan</a>
            @foreach ($guides as $group => $items)
                <h4>{{ $group }}</h4>
                <ul>@foreach ($items as $k => $it)<li><a href="{{ route('landing.guide', $k) }}" class="{{ $key === $k ? 'on' : '' }}">{{ $it['title'] }}</a></li>@endforeach</ul>
            @endforeach
        </aside>
        <article class="ln-doc-body">
            @if ($g)
                <div class="ln-doc-tools"><button type="button" class="ln-btn ln-btn-ghost ln-btn-sm" onclick="window.print()">Cetak / simpan PDF</button> <a href="#demo-cta" class="ln-btn ln-btn-sm">Coba di data demo</a></div>
                <h1>{{ $g['title'] }}</h1>
                {!! $g['html'] !!}
                <div class="ln-card ln-doc-cta" id="demo-cta"><b>Ingin mencoba langsung?</b> <a href="{{ route('landing') }}#demo">Pilih data demo lalu masuk sebagai tamu →</a></div>
            @else
                <h1>Panduan penggunaan</h1>
                <p class="ln-lead">Penjelasan setiap layar SIAGA: apa fungsinya, apa yang perlu diisi, dan contoh tangkapan layarnya. Mulai dari <a href="{{ route('landing.guide', 'implementasi') }}">Panduan implementasi</a> bila Anda sedang menyiapkan penerapan.</p>
                @foreach ($guides as $group => $items)
                    <h2>{{ $group }}</h2>
                    <div class="ln-grid ln-grid-2">
                        @foreach ($items as $k => $it)
                            <a class="ln-card ln-card-link" href="{{ route('landing.guide', $k) }}"><b>{{ $it['title'] }}</b><span>{{ \Illuminate\Support\Str::limit($it['summary'], 140) }}</span></a>
                        @endforeach
                    </div>
                @endforeach
            @endif
        </article>
    </div>
</section>
@endsection
