@php $sets = \App\Support\GuestAccess::enabled() ? app(\App\Http\Controllers\LandingController::class)->datasets() : []; @endphp
@if ($sets)
<div class="ac-guest-login">
    <form method="post" action="{{ route('landing.guest') }}" class="ac-guest-login-box">
        @csrf
        <p class="ac-guest-login-title">Coba SIAGA tanpa akun</p>
        <p class="ac-guest-login-label">Pilih perusahaan demo:</p>
        <div class="ac-guest-login-list" role="radiogroup">
            @foreach ($sets as $i => $s)
                <label class="ac-guest-login-opt">
                    <input type="radio" name="dataset" value="{{ $s['code'] }}" @checked($i === 0)>
                    <span><b>{{ $s['title'] }}</b>@if ($s['sector'])<small>{{ $s['sector'] }}</small>@endif</span>
                </label>
            @endforeach
        </div>
        <button type="submit" class="ac-guest-login-btn">Masuk sebagai tamu</button>
        <p class="ac-guest-login-note">Hanya melihat · data contoh / proyeksi · <a href="{{ route('landing') }}">Tentang SIAGA</a></p>
    </form>
    <div class="ac-guest-login-or"><span>atau masuk dengan akun staf</span></div>
</div>
@endif
