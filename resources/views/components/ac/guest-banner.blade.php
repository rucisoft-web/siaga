@php $p = app(\App\Support\CurrentProperty::class)->get(); @endphp
<div class="ac-guest-banner" role="status">
    <span>Anda menjelajah <b>{{ $p?->name }}</b> sebagai <b>tamu</b>: hanya melihat, data contoh / proyeksi.</span>
    <span class="ac-guest-actions">
        @if (Route::has('landing'))<a href="{{ route('landing') }}#demo">Ganti data demo</a>@endif
        <a href="{{ \App\Filament\Pages\DasborPimpinan::getUrl() }}">Dasbor pimpinan</a>
        <a href="{{ route('landing.guides') }}">Panduan</a>
    </span>
</div>
