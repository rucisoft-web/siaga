@extends('landing.layout')
@php
    $pick = request('demo');
    $num = fn ($v) => number_format((int) $v, 0, ',', '.');
@endphp
@section('content')

{{-- A · ATTENTION --}}
<section class="ln-hero">
    <div class="ln-wrap ln-hero-in">
        <div>
            <p class="ln-eyebrow">Untuk hotel, rumah sakit, apotek &amp; klinik</p>
            <h1>Setiap aset tercatat. Setiap kerusakan tertangani. Setiap keputusan pakai angka.</h1>
            <p class="ln-lead">SIAGA menyatukan register aset berlabel QR, laporan kerusakan dari HP, work order dengan persetujuan biaya, dan dasbor pimpinan, dalam satu aplikasi yang dipakai dari lantai kamar sampai ruang direksi.</p>
            <div class="ln-cta">
                <a href="#demo" class="ln-btn">Jelajahi data demo</a>
                <a href="{{ route('landing.guide', 'implementasi') }}" class="ln-btn ln-btn-ghost">Lihat panduan implementasi</a>
            </div>
            <ul class="ln-proof">
                <li><b>1 label QR</b> per aset, dicetak di lapangan</li>
                <li><b>&lt; 1 menit</b> melapor kerusakan dari HP</li>
                <li><b>3 kelompok grafik</b> untuk keputusan pimpinan</li>
            </ul>
        </div>
        <figure class="ln-shot">
            <img src="{{ asset('landing/dasbor.png') }}" alt="Dasbor pimpinan SIAGA: kondisi aset, kinerja perbaikan, biaya" loading="eager">
        </figure>
    </div>
</section>

{{-- I · INTEREST --}}
<section class="ln-sec">
    <div class="ln-wrap">
        <h2>Masalah yang sering terjadi</h2>
        <div class="ln-grid ln-grid-4">
            <div class="ln-card"><h3>Aset tidak jelas</h3><p>Daftar aset tersebar di Excel, tidak ada yang tahu jumlah, lokasi, dan kondisi sebenarnya. Audit selalu menemukan selisih.</p></div>
            <div class="ln-card"><h3>Laporan hilang di chat</h3><p>Kerusakan dilaporkan lewat WhatsApp atau telepon. Tidak tercatat siapa menangani, kapan selesai, dan apakah berulang.</p></div>
            <div class="ln-card"><h3>Biaya tidak terlacak</h3><p>Suku cadang dan jasa vendor dibayar, tapi tidak tertaut ke aset. Aset boros biaya tidak pernah terlihat.</p></div>
            <div class="ln-card"><h3>Pimpinan menebak</h3><p>Keputusan perbaiki atau ganti, tambah teknisi, atau anggaran tahun depan, diambil tanpa data yang bisa ditelusuri.</p></div>
        </div>
    </div>
</section>

<section class="ln-sec ln-alt" id="cara-kerja">
    <div class="ln-wrap">
        <h2>Cara kerja SIAGA</h2>
        <ol class="ln-steps">
            <li><span>1</span><h3>Data &amp; label</h3><p>Impor master aset, susun isi standar per tipe kamar/ruang, lalu petugas mendata per kamar dengan Mobile Terminal dan mencetak label QR di tempat.</p><img src="{{ asset('help/img/pendataan-kamar.png') }}" alt="Pendataan per kamar" loading="lazy"></li>
            <li><span>2</span><h3>Lapor</h3><p>Siapa pun memindai QR pada aset atau memilih lokasi, menulis gejala, dan memotret. Prioritas dan target waktu (SLA) ditetapkan otomatis.</p><img src="{{ asset('help/img/service-requests-create.png') }}" alt="Lapor kerusakan" loading="lazy"></li>
            <li><span>3</span><h3>Kerjakan &amp; verifikasi</h3><p>Work order ditugaskan ke teknisi, biaya disetujui sesuai batas wewenang, hasil diverifikasi, dan kamar yang diblokir dikonfirmasi siap dipakai kembali.</p><img src="{{ asset('help/img/work-orders-view.png') }}" alt="Work order" loading="lazy"></li>
            <li><span>4</span><h3>Putuskan</h3><p>Dasbor pimpinan menunjukkan aset berisiko, SLA, MTTR, backlog, biaya per bulan, aset boros biaya, dan hari-kamar yang hilang.</p><img src="{{ asset('landing/dasbor-biaya.png') }}" alt="Grafik biaya" loading="lazy"></li>
        </ol>
    </div>
</section>

{{-- D · DESIRE --}}
<section class="ln-sec">
    <div class="ln-wrap">
        <h2>Manfaat untuk setiap peran</h2>
        <div class="ln-grid ln-grid-4">
            <div class="ln-card"><h3>Pimpinan</h3><ul><li>Sorotan otomatis: aset berisiko, SLA, aset yang lebih murah diganti</li><li>Filter per gedung, rumah sakit, atau wilayah outlet</li><li>Cetak dasbor untuk rapat</li></ul></div>
            <div class="ln-card"><h3>Engineering / IPSRS</h3><ul><li>Antrian kerja per prioritas dan target waktu</li><li>Riwayat lengkap tiap aset dari scan QR</li><li>Kerusakan berulang terlihat jelas</li></ul></div>
            <div class="ln-card"><h3>Keuangan &amp; aset</h3><ul><li>Nilai aset dan biaya pemeliharaan per aset</li><li>Persetujuan biaya berjenjang sesuai batas</li><li>Mutasi aset tercatat dan bisa diaudit</li></ul></div>
            <div class="ln-card"><h3>Staf pelapor</h3><ul><li>Lapor dari HP dalam hitungan detik</li><li>Status laporan bisa dipantau</li><li>Tidak perlu pelatihan panjang</li></ul></div>
        </div>
        <h2 class="ln-mt">Satu aplikasi, beragam industri</h2>
        <div class="ln-grid ln-grid-3">
            <div class="ln-card ln-ind"><h3>Hotel &amp; MICE</h3><p>Kamar per tipe, ruang meeting, kitchen, laundry, power house. Blokir kamar tidak terjual saat perbaikan.</p></div>
            <div class="ln-card ln-ind"><h3>Rumah sakit</h3><p>Kamar rawat inap per kelas, IGD, ICU, NICU, bedah sentral, radiologi, hemodialisa. Alat medis, kalibrasi, gas medis.</p></div>
            <div class="ln-card ln-ind"><h3>Apotek, klinik &amp; lab</h3><p>Puluhan outlet dengan isi standar per tipe, cold chain obat, POS kasir, alat klinik dan laboratorium.</p></div>
        </div>
    </div>
</section>

<section class="ln-sec ln-alt" id="workshop">
    <div class="ln-wrap">
        <h2>Workshop pengenalan 2 hari</h2>
        <p class="ln-lead">Jelajahi data demo sebelum workshop agar sesi praktik lebih cepat. Peserta yang disarankan: pimpinan, kepala engineering/IPSRS, asset controller &amp; keuangan, serta perwakilan Front Office, keperawatan, atau apoteker.</p>
        <div class="ln-grid ln-grid-2">
            <div class="ln-card"><h3>Hari 1 · Fondasi &amp; pendataan</h3><ol class="ln-agenda">
                <li>Masalah aset &amp; gangguan: studi kasus dan cara menghitung kerugiannya</li>
                <li>Konsep SIAGA: register aset, label QR, alur laporan → work order → verifikasi</li>
                <li>Praktik: lokasi, tipe kamar/ruang, template isi standar, impor master aset</li>
                <li>Praktik lapangan: pendataan per kamar dengan Mobile Terminal &amp; printer portable</li>
                <li>Cek kualitas data, selisih, dan aktivasi aset</li>
            </ol></div>
            <div class="ln-card"><h3>Hari 2 · Operasi &amp; keputusan</h3><ol class="ln-agenda">
                <li>Lapor kerusakan dari HP, triase, prioritas &amp; SLA</li>
                <li>Work order: penugasan, persetujuan biaya, verifikasi, blokir kamar, mutasi aset</li>
                <li>Dasbor pimpinan &amp; laporan: membaca angka untuk keputusan perbaiki atau ganti</li>
                <li>Rencana implementasi 8 minggu: organisasi, RACI, komunikasi, risiko, pengadaan</li>
                <li>Rencana tindak lanjut di perusahaan peserta</li>
            </ol></div>
        </div>
        <p><a href="{{ route('landing.guide', 'implementasi') }}">Baca panduan implementasi lengkap →</a></p>
    </div>
</section>

{{-- A · ACTION --}}
<section class="ln-sec ln-action" id="demo">
    <div class="ln-wrap">
        <h2>Coba sekarang: pilih data demo, lalu masuk sebagai tamu</h2>
        <p class="ln-lead">Setiap data demo adalah perusahaan terpisah dengan data contoh / proyeksi yang lengkap: pendataan berjalan, laporan kerusakan, work order di semua status, biaya, mutasi, dan riwayat 6 bulan. Akun tamu hanya bisa melihat, tidak bisa mengubah data.</p>
        @if (session('landing_error'))<p class="ln-error">{{ session('landing_error') }}</p>@endif
        @if ($datasets)
            <form method="post" action="{{ route('landing.guest') }}" class="ln-demo" x-data>
                @csrf
                <div class="ln-grid ln-grid-3">
                    @foreach ($datasets as $i => $s)
                        <label class="ln-pick">
                            <input type="radio" name="dataset" value="{{ $s['code'] }}" @checked($pick ? $pick === $s['key'] : $i === 0) required>
                            <span class="ln-pick-body">
                                <span class="ln-pick-sector">{{ $s['sector'] }}</span>
                                <b>{{ $s['title'] }}</b>
                                @if ($s['summary'])<span class="ln-pick-sum">{{ $s['summary'] }}</span>@endif
                                @if ($s['stats'])
                                    <span class="ln-pick-stats"><span><b>{{ $num($s['stats']['aset']) }}</b> aset</span><span><b>{{ $num($s['stats']['ruang']) }}</b> kamar/outlet</span><span><b>{{ $num($s['stats']['wo']) }}</b> work order</span></span>
                                @endif
                                @if ($s['source'])<span class="ln-pick-src">Sumber: {{ $s['source'] }}</span>@endif
                            </span>
                        </label>
                    @endforeach
                </div>
                <div class="ln-cta">
                    @if ($guest)
                        <button type="submit" class="ln-btn ln-btn-lg">Masuk sebagai tamu</button>
                    @endif
                    <a href="{{ url('/admin/login') }}" class="ln-btn ln-btn-ghost">Masuk staf</a>
                </div>
                @unless ($guest)<p class="ln-muted">Mode tamu tidak aktif di server ini. Hubungi admin untuk akun demo.</p>@endunless
            </form>
        @else
            <p class="ln-muted">Belum ada data demo terpasang. Admin dapat memasangnya dengan <code>php artisan siaga:demo-data all</code>.</p>
            <div class="ln-cta"><a href="{{ url('/admin/login') }}" class="ln-btn">Masuk staf</a></div>
        @endif
    </div>
</section>

<section class="ln-sec">
    <div class="ln-wrap">
        <h2>Panduan penggunaan per layar</h2>
        <p class="ln-lead">Di aplikasi, setiap layar punya tombol <b>? Panduan</b> di pojok kanan atas. Semua panduan juga bisa dibaca di sini tanpa masuk.</p>
        <div class="ln-grid ln-grid-3">
            @foreach ($guides as $group => $items)
                <div class="ln-card"><h3>{{ $group }}</h3><ul class="ln-links">
                    @foreach ($items as $k => $g)<li><a href="{{ route('landing.guide', $k) }}">{{ $g['title'] }}</a></li>@endforeach
                </ul></div>
            @endforeach
        </div>
    </div>
</section>
@endsection
