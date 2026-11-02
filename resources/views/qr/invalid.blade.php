<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Label tidak berlaku · {{ \App\Support\Branding::appName() }}</title>
<link rel="icon" href="{{ asset('favicon.png') }}">
<style>
    {!! \App\Support\Branding::cssVars() !!}
    body { margin:0; font-family: Inter, system-ui, sans-serif; background:#F3F4F6; color:#111827; display:flex; min-height:100vh; align-items:center; justify-content:center; padding:1rem; border-top: 6px solid var(--p); }
    .card { max-width:26rem; background:#fff; border:1px solid #9AA3B0; border-top:3px solid var(--a); border-radius:2px; padding:1.6rem; text-align:left; }
    .hotel { font-size:.7rem; letter-spacing:.16em; text-transform:uppercase; color:#5B6472; font-weight:700; padding-bottom:.6rem; border-bottom:1px solid #D5D9E0; }
    h1 { font-size:1.15rem; margin:.9rem 0 .4rem; color:#B42318; }
    p { color:#374151; font-size:.92rem; line-height:1.5; }
    a { display:inline-block; margin-top:.8rem; padding:.6rem 1rem; border-radius:2px; background:var(--p); color:#fff; text-decoration:none; font-weight:700; text-transform:uppercase; letter-spacing:.06em; font-size:.8rem; }
</style>
</head>
<body>
<div class="card">
    @if ($logo = \App\Support\Branding::logoUrl())<img src="{{ $logo }}" alt="" style="height:40px;margin-bottom:.6rem">@endif
    <div class="hotel">{{ \App\Support\Branding::hotelName() }} · {{ \App\Support\Branding::appName() }}</div>
    <h1>Label tidak dapat digunakan</h1>
    <p>{{ $message ?? 'Label ini sudah tidak berlaku.' }}</p>
    <p>Bila ada kerusakan, laporkan melalui aplikasi atau hubungi Engineering.</p>
    <a href="{{ url('/admin') }}">Buka aplikasi</a>
</div>
</body>
</html>
