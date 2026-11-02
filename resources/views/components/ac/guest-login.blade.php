@php $sets = \App\Support\GuestAccess::enabled() ? app(\App\Http\Controllers\LandingController::class)->datasets() : []; @endphp
@if ($sets)
<div class="ac-guest-login">
    <div class="ac-guest-login-or"><span>atau</span></div>
    <form method="post" action="{{ route('landing.guest') }}">
        @csrf
        @if (count($sets) > 1)
            <label class="ac-guest-login-label" for="ac-guest-ds">Coba tanpa akun: pilih data demo</label>
            <select id="ac-guest-ds" name="dataset" class="ac-guest-login-select">
                @foreach ($sets as $s)<option value="{{ $s['code'] }}">{{ $s['title'] }} · {{ $s['sector'] }}</option>@endforeach
            </select>
        @else
            <input type="hidden" name="dataset" value="{{ $sets[0]['code'] }}">
            <p class="ac-guest-login-label">Coba tanpa akun dengan data demo <b>{{ $sets[0]['title'] }}</b></p>
        @endif
        <button type="submit" class="ac-guest-login-btn">Masuk sebagai tamu</button>
        <p class="ac-guest-login-note">Hanya melihat. Data contoh / proyeksi. <a href="{{ route('landing') }}">Tentang SIAGA</a></p>
    </form>
</div>
@endif
