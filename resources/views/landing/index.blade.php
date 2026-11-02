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
                @if ($guest && $datasets)
                    <form method="post" action="{{ route('landing.guest') }}" class="ln-inline">
                        @csrf
                        <input type="hidden" name="dataset" value="{{ $datasets[0]['code'] }}">
                        <button type="submit" class="ln-btn ln-btn-lg">Masuk sebagai tamu</button>
                    </form>
                    <a href="#masalah" class="ln-btn ln-btn-ghost ln-btn-lg">Kenali masalahnya</a>
                @else
                    <a href="#demo" class="ln-btn">Jelajahi data demo</a>
                    <a href="#masalah" class="ln-btn ln-btn-ghost">Kenali masalahnya</a>
                @endif
            </div>
            @if ($guest && $datasets)<p class="ln-muted">Langsung mencoba semua layar dengan data demo {{ $datasets[0]['title'] }}, tanpa akun. Hanya melihat.</p>@endif
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
<section class="ln-sec" id="cek">
    <div class="ln-wrap">
        <h2>Apakah ini terjadi di perusahaan Anda?</h2>
        <p class="ln-lead">Centang yang sesuai. Semakin banyak yang tercentang, semakin besar nilai aset dan pendapatan yang diam-diam hilang.</p>
        <div class="ln-check" data-check>
            @foreach ([
                'Jumlah dan lokasi aset di Excel berbeda dengan kondisi di lapangan',
                'Kerusakan dilaporkan lewat WhatsApp atau telepon, lalu terlupa',
                'Aset yang sama rusak lagi dan lagi, tanpa ada yang menyadari',
                'Biaya perbaikan tidak bisa ditunjukkan per aset',
                'Kamar atau ruang tidak bisa dipakai lebih lama dari seharusnya',
                'Pimpinan menunggu laporan bulanan untuk tahu kondisi aset',
            ] as $i => $q)
                <label class="ln-check-item"><input type="checkbox"><span>{{ $q }}</span></label>
            @endforeach
        </div>
        <p class="ln-check-result" data-check-result aria-live="polite"></p>
    </div>
</section>

<section class="ln-sec ln-alt" id="masalah">
    <div class="ln-wrap">
        <h2>Masalah pengelolaan aset tetap, penyebabnya, dan solusi SIAGA</h2>
        <p class="ln-lead">Aset tetap (bangunan, mesin, peralatan, perabot, kendaraan, IT) adalah porsi terbesar kekayaan perusahaan jasa. Masalahnya jarang karena orangnya tidak peduli. Masalahnya ada pada cara kerja yang tidak memberi informasi yang tepat ke orang yang tepat pada waktu yang tepat.</p>
        @php
            $rows = [
                ['Aset tidak tercatat lengkap dan tidak akurat', 'Register di Excel per departemen, tanpa identitas fisik pada barang. Pemindahan dan penggantian tidak dicatat. Inventarisasi jarang dilakukan karena memakan waktu. Tidak jelas siapa pemilik datanya.',
                    'Satu register untuk semua aset dengan <b>label QR unik</b>. Pendataan per kamar/ruang memakai <b>daftar isi standar</b>, jadi yang hilang atau berlebih langsung terlihat. Label dicetak di lapangan dengan printer portable. Aset baru aktif setelah diverifikasi Asset Controller.', 'help/img/pendataan-progres.png', 'Progres pendataan per gedung dan lantai'],
                ['Laporan kerusakan tercecer dan respons lambat', 'Laporan lewat chat, telepon, atau kertas. Tidak ada antrean dan prioritas, tidak ada target waktu, dan pelapor tidak tahu laporannya sedang ditangani atau tidak.',
                    'Siapa pun <b>scan QR atau pilih lokasi</b> dari HP, tulis gejala, lampirkan foto. Prioritas dan <b>target waktu (SLA)</b> dihitung otomatis dari dampaknya. Laporan ganda digabung. Pelapor bisa memantau status.', 'landing/tur-lapor-hp.png', 'Lapor kerusakan dari HP'],
                ['Kerusakan berulang dan umur aset pendek', 'Riwayat perbaikan tidak tersimpan per aset. Diagnosis dan akar masalah tidak ditulis. Hasil pekerjaan tidak diverifikasi. Pemeliharaan baru dilakukan setelah rusak.',
                    'Setiap aset punya <b>kartu dengan riwayat lengkap</b>. Teknisi wajib mencatat diagnosis, akar masalah, dan uji fungsi. Hasil diverifikasi atasan. <b>Kerusakan berulang terdeteksi otomatis</b>, dan pekerjaan preventif tercatat.', 'landing/tur-aset.png', 'Kartu aset dengan riwayat perbaikan'],
                ['Biaya pemeliharaan bocor dan tidak terkendali', 'Pembelian suku cadang dan jasa vendor tidak tertaut ke aset atau pekerjaan. Persetujuan tidak berjenjang. Tidak ada yang membandingkan biaya perbaikan dengan harga aset baru.',
                    'Biaya material, vendor, dan tenaga dicatat <b>per work order</b>. Persetujuan mengikuti <b>batas wewenang</b> (Chief Engineer, Finance, GM), lalu difinalkan Finance. Rasio biaya terhadap harga perolehan memberi sinyal <b>perbaiki atau ganti</b>.', 'landing/tur-wo.png', 'Work order dengan biaya dan persetujuan'],
                ['Pendapatan hilang karena kamar atau ruang tidak bisa dipakai', 'Koordinasi Engineering dengan Front Office lemah. Kamar rusak tidak segera diblokir, atau lupa dibuka kembali setelah selesai. Kerugiannya tidak pernah dihitung.',
                    'Blokir kamar diusulkan langsung dari work order, <b>dikonfirmasi Front Office</b>, dan dilepas setelah pekerjaan diverifikasi. Total <b>hari-kamar yang hilang</b> tampil di dasbor pimpinan.', 'help/img/room-blocks.png', 'Blokir kamar terkait perbaikan'],
                ['Pimpinan memutuskan tanpa data', 'Laporan disusun manual, terlambat, dan angkanya tidak bisa ditelusuri. Tidak ada indikator baku seperti SLA, MTTR, atau backlog.',
                    '<b>Dasbor pimpinan</b> menampilkan kondisi A–E, aset berisiko dan nilai gantinya, SLA, MTTR, backlog, biaya per bulan, aset boros biaya, serta <b>sorotan otomatis</b>. Setiap angka bisa ditelusuri ke daftar sumbernya.', 'landing/dasbor.png', 'Dasbor pimpinan'],
                ['Tidak jelas siapa mengubah apa', 'File dibagikan tanpa hak akses. Perubahan data tidak tercatat, sehingga sulit diaudit dan mudah disalahgunakan.',
                    '<b>17 peran dan 36 izin</b> (tolak secara bawaan), <b>log audit</b> setiap perubahan, wajib ganti sandi, batas percobaan login, dan verifikasi dua langkah.', 'help/img/roles.png', 'Peran dan izin'],
            ];
        @endphp
        <div class="ln-psr">
            @foreach ($rows as $i => [$m, $why, $sol, $img, $cap])
                <article class="ln-psr-row">
                    <div class="ln-psr-text">
                        <span class="ln-psr-no">{{ $i + 1 }}</span>
                        <h3>{{ $m }}</h3>
                        <div class="ln-psr-cols">
                            <div class="ln-psr-why"><h4>Kenapa terjadi</h4><p>{{ $why }}</p></div>
                            <div class="ln-psr-sol"><h4>Solusi SIAGA</h4><p>{!! $sol !!}</p></div>
                        </div>
                    </div>
                    <figure class="ln-psr-img"><a href="{{ asset($img) }}" target="_blank" rel="noopener"><img src="{{ asset($img) }}" alt="{{ $cap }}" loading="lazy"></a><figcaption>{{ $cap }}</figcaption></figure>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="ln-sec" id="cara-kerja">
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

{{-- grafik nyata dari data demo --}}
@if ($showcase)
@php
    $k = $showcase['kpi'];
    $jt = fn ($v) => $v >= 1e9 ? 'Rp '.number_format($v / 1e9, 1, ',', '.').' M' : 'Rp '.number_format($v / 1e6, 1, ',', '.').' jt';
    $sd = array_sum($showcase['survey']['done']); $sa = $sd + array_sum($showcase['survey']['todo']);
    $gradeColors = ['A' => '#1E7B45', 'B' => '#7FB069', 'C' => '#E0A526', 'D' => '#D9622B', 'E' => '#B42318', '?' => '#B7BEC9'];
    $gradeNames = \App\Services\LeadershipService::GRADES;
    $condTotals = collect($showcase['cond']['series'])->map(fn ($v) => array_sum($v))->filter();
    $lc = [
        'cond' => ['type' => 'doughnut', 'data' => ['labels' => $condTotals->keys()->map(fn ($g) => $gradeNames[$g])->values(), 'datasets' => [['data' => $condTotals->values(), 'backgroundColor' => $condTotals->keys()->map(fn ($g) => $gradeColors[$g])->values()]]]],
        'weekly' => ['type' => 'bar', 'data' => ['labels' => $showcase['weekly']['labels'], 'datasets' => [
            ['type' => 'line', 'label' => 'Work order masuk', 'data' => $showcase['weekly']['in'], 'borderColor' => '#B42318', 'backgroundColor' => '#B42318', 'tension' => .3],
            ['label' => 'Selesai', 'data' => $showcase['weekly']['out'], 'backgroundColor' => 'P']]]],
        'cost' => ['type' => 'bar', 'money' => true, 'stacked' => true, 'data' => ['labels' => $showcase['cost']['labels'], 'datasets' => collect($showcase['cost']['series'])->map(fn ($v, $n) => ['label' => $n, 'data' => $v])->values()->map(fn ($d, $i) => $d + ['backgroundColor' => ['P', 'A', '#7FB069', '#B7BEC9'][$i] ?? '#999'])->all()]],
        'sla' => ['type' => 'bar', 'stacked' => true, 'data' => ['labels' => $showcase['sla']['labels'], 'datasets' => [['label' => 'Tepat waktu', 'data' => $showcase['sla']['met'], 'backgroundColor' => '#1E7B45'], ['label' => 'Terlambat', 'data' => $showcase['sla']['missed'], 'backgroundColor' => '#B42318']]]],
    ];
@endphp
<section class="ln-sec ln-alt" id="angka">
    <div class="ln-wrap">
        <h2>Lihat angkanya: dasbor dari data demo {{ $showcase['title'] }}</h2>
        <p class="ln-lead">Grafik di bawah dihitung langsung oleh SIAGA dari data demo (contoh / proyeksi): register aset, hasil pendataan, laporan kerusakan, work order, dan biaya. Begini tampilan yang akan dilihat pimpinan setiap hari.</p>
        <div class="ln-kpis">
            <div><b>{{ number_format($k['assets'], 0, ',', '.') }}</b><span>aset terdaftar</span></div>
            <div><b>{{ $sa ? round($sd / $sa * 100) : 0 }}%</b><span>kamar/ruang selesai didata</span></div>
            <div><b class="bad">{{ number_format($k['risk_count'], 0, ',', '.') }}</b><span>aset berisiko (D/E)</span></div>
            <div><b>{{ $k['sla_pct'] ?? '—' }}%</b><span>perbaikan tepat waktu</span></div>
            <div><b>{{ $k['mttr_h'] !== null ? number_format($k['mttr_h'], 1, ',', '.').' jam' : '—' }}</b><span>rata-rata waktu pulih</span></div>
            <div><b>{{ $jt($k['cost_6m']) }}</b><span>biaya pemeliharaan 6 bulan</span></div>
        </div>
        <div class="ln-grid ln-grid-2 ln-charts">
            <div class="ln-card"><h3>Kondisi aset (A–E)</h3><p>Sebaran kondisi hasil pendataan. Porsi D/E adalah kandidat perbaikan besar atau penggantian.</p><div class="ln-canvas"><canvas data-lc="cond"></canvas></div></div>
            <div class="ln-card"><h3>Work order masuk vs selesai per minggu</h3><p>Garis di atas batang berarti backlog bertambah dan kapasitas teknisi perlu ditinjau.</p><div class="ln-canvas"><canvas data-lc="weekly"></canvas></div></div>
            <div class="ln-card"><h3>Biaya pemeliharaan per bulan</h3><p>Material, jasa vendor, dan tenaga internal, semuanya tertaut ke aset dan work order.</p><div class="ln-canvas"><canvas data-lc="cost"></canvas></div></div>
            <div class="ln-card"><h3>Ketepatan waktu per prioritas (SLA)</h3><p>Perbaikan yang pulih sebelum target waktu dibandingkan dengan yang terlambat.</p><div class="ln-canvas"><canvas data-lc="sla"></canvas></div></div>
        </div>
        <div class="ln-cta"><a href="{{ route('landing', ['demo' => $showcase['key']]) }}#demo" class="ln-btn">Buka dasbor lengkapnya sebagai tamu</a></div>
    </div>
</section>
<script src="{{ asset('js/vendor/chart.umd.min.js') }}" defer></script>
<script>
window.addEventListener('DOMContentLoaded', function () {
    const charts = @json($lc);
    const css = getComputedStyle(document.documentElement);
    const P = css.getPropertyValue('--p').trim() || '#1F3A5F', A = css.getPropertyValue('--a').trim() || '#B8860B';
    const ink = css.getPropertyValue('--ink2').trim() || '#4B5563', grid = 'rgba(128,128,128,.15)';
    const col = c => c === 'P' ? P : c === 'A' ? A : c;
    const rp = v => v >= 1e6 ? 'Rp ' + (v / 1e6).toLocaleString('id-ID', {maximumFractionDigits: 0}) + ' jt' : 'Rp ' + Math.round(v).toLocaleString('id-ID');
    function go() {
        if (!window.Chart) return setTimeout(go, 60);
        Chart.defaults.color = ink;
        document.querySelectorAll('canvas[data-lc]').forEach(el => {
            const c = charts[el.dataset.lc];
            c.data.datasets.forEach(ds => ['backgroundColor', 'borderColor'].forEach(k => { if (ds[k]) ds[k] = Array.isArray(ds[k]) ? ds[k].map(col) : col(ds[k]); }));
            const scales = c.type === 'doughnut' ? {} : { x: { stacked: !!c.stacked, grid: { color: grid } }, y: { stacked: !!c.stacked, beginAtZero: true, grid: { color: grid }, ticks: c.money ? { callback: rp } : {} } };
            new Chart(el, { type: c.type, data: c.data, options: { responsive: true, maintainAspectRatio: false, scales,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } }, tooltip: c.money ? { callbacks: { label: x => x.dataset.label + ': ' + rp(x.raw) } } : {} } } });
        });
    }
    go();
});
</script>
@endif

{{-- tur layar --}}
<section class="ln-sec" id="tur">
    <div class="ln-wrap">
        <h2>Tur layar SIAGA</h2>
        <p class="ln-lead">Tangkapan layar asli dari data demo. Klik gambar untuk memperbesar.</p>
        @php
            $tour = [
                ['landing/tur-aset.png', 'Kartu aset', 'Identitas, lokasi, kondisi, nilai, label QR, dan riwayat.', 'wide'],
                ['landing/tur-wo.png', 'Work order', 'Penugasan, biaya, persetujuan, verifikasi, jejak status.', 'wide'],
                ['landing/tur-beranda-hp.png', 'Beranda di HP', 'Tugas hari ini, scan, dan lapor dari bilah bawah.', 'phone'],
                ['landing/tur-pendataan-hp.png', 'Pendataan per kamar', 'Checklist isi kamar, kondisi A–E, cetak label di tempat.', 'phone'],
                ['landing/tur-lapor-hp.png', 'Lapor kerusakan', 'Pilih aset atau lokasi, tulis gejala, foto. Selesai.', 'phone'],
                ['landing/tur-label.png', 'Cetak label QR', 'Langsung ke printer portable Bluetooth atau lembar A4.', 'wide'],
                ['landing/dasbor-biaya.png', 'Biaya & dampak', 'Biaya per bulan, per kategori, dan hari-kamar yang hilang.', 'wide'],
            ];
        @endphp
        <div class="ln-tour">
            @foreach ($tour as [$img, $t, $c, $kind])
                @if (is_file(public_path($img)))
                    <figure class="ln-tour-item {{ $kind }}"><a href="{{ asset($img) }}" target="_blank" rel="noopener"><img src="{{ asset($img) }}" alt="{{ $t }}" loading="lazy"></a><figcaption><b>{{ $t }}</b><span>{{ $c }}</span></figcaption></figure>
                @endif
            @endforeach
        </div>
    </div>
</section>

{{-- D · DESIRE --}}
<section class="ln-sec ln-alt">
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
<script>
(function () {
    const box = document.querySelector('[data-check]'), out = document.querySelector('[data-check-result]');
    if (!box) return;
    const msg = n => n === 0 ? '' : n <= 2 ? n + ' dari 6: sudah mulai terasa. SIAGA membantu menutup celah ini sejak awal.' : n <= 4 ? n + ' dari 6: aset Anda kemungkinan besar sudah menimbulkan biaya tersembunyi. Lihat penyebab dan solusinya di bawah.' : n + ' dari 6: saatnya berbenah. Coba data demo dan ikuti workshop 2 hari.';
    box.addEventListener('change', () => { out.textContent = msg(box.querySelectorAll('input:checked').length); });
})();
</script>
@endsection
