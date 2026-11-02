@php $property = app(\App\Support\CurrentProperty::class)->get(); @endphp
@if ($property)
    <span class="arah-context" title="Properti aktif · waktu ditampilkan {{ \App\Support\Fmt::tz() }}">
        <span class="arah-dot" style="background:var(--a)"></span>
        {{ $property->code }} <span style="opacity:.5">|</span> {{ \App\Support\Branding::timezoneLabel() }}
    </span>
@endif
