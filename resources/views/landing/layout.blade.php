<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SIAGA — Sistem Informasi Aset & Gangguan')</title>
    <meta name="description" content="SIAGA: register aset berlabel QR, laporan kerusakan dari HP, work order, biaya, dan dasbor pimpinan untuk hotel, rumah sakit, apotek & klinik.">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}?v={{ @filemtime(public_path('css/landing.css')) }}">
</head>
<body>
<header class="ln-top">
    <div class="ln-wrap ln-top-in">
        <a href="{{ route('landing') }}" class="ln-brand"><span class="ln-logo">S</span><span><b>SIAGA</b><small>Sistem Informasi Aset &amp; Gangguan</small></span></a>
        <nav class="ln-nav">
            <a href="{{ route('landing') }}#masalah">Masalah &amp; solusi</a>
            <a href="{{ route('landing') }}#tur">Tur layar</a>
            <a href="{{ route('landing') }}#workshop">Workshop</a>
            <a href="{{ route('landing.guide', 'implementasi') }}">Panduan implementasi</a>
            <a href="{{ route('landing.guides') }}">Panduan penggunaan</a>
            <a href="{{ url('/admin/login') }}">Masuk staf</a>
            <a href="{{ route('landing') }}#demo" class="ln-btn ln-btn-sm">Masuk sebagai tamu</a>
        </nav>
    </div>
</header>
@yield('content')
<footer class="ln-foot">
    <div class="ln-wrap">
        <p><b>SIAGA</b> · Sistem Informasi Aset &amp; Gangguan. Situs demo &amp; pra-workshop: seluruh data perusahaan di sini adalah <b>data contoh / proyeksi</b>, bukan data resmi perusahaan yang bersangkutan.</p>
        <p><a href="{{ url('/admin/login') }}">Masuk staf</a> · <a href="{{ route('landing.guide', 'implementasi') }}">Panduan implementasi</a> · <a href="{{ route('landing.guides') }}">Panduan penggunaan</a></p>
    </div>
</footer>
</body>
</html>
