@php
    $property = app(\App\Support\CurrentProperty::class)->get();
    $switchable = \App\Support\CurrentProperty::canSwitch(auth()->user()) ? \App\Models\Property::where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']) : collect();
@endphp
@if ($property)
    @if ($switchable->count() > 1)
        <details class="ac-psw">
            <summary class="arah-context" title="Perusahaan aktif · klik untuk berpindah">
                <span class="arah-dot" style="background:var(--a)"></span>
                {{ $property->code }} <span style="opacity:.5">|</span> {{ \App\Support\Branding::timezoneLabel() }} ▾
            </summary>
            <div class="ac-psw-menu">
                <p>Pindah perusahaan</p>
                @foreach ($switchable as $p)
                    <form method="post" action="{{ route('property.switch') }}">
                        @csrf
                        <input type="hidden" name="property_id" value="{{ $p->id }}">
                        <input type="hidden" name="back" value="{{ request()->getRequestUri() }}">
                        <button type="submit" @class(['on' => $p->id === $property->id])><b>{{ $p->code }}</b> {{ $p->name }}</button>
                    </form>
                @endforeach
                @if (\App\Filament\Pages\DasborGrup::canAccess())<a href="{{ \App\Filament\Pages\DasborGrup::getUrl() }}">Dasbor grup (semua perusahaan) →</a>@endif
            </div>
        </details>
    @else
        <span class="arah-context" title="Properti aktif · waktu ditampilkan {{ \App\Support\Fmt::tz() }}">
            <span class="arah-dot" style="background:var(--a)"></span>
            {{ $property->code }} <span style="opacity:.5">|</span> {{ \App\Support\Branding::timezoneLabel() }}
        </span>
    @endif
@endif
