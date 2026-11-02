<?php

namespace App\Http\Controllers;

use App\Filament\Pages\DasborPimpinan;
use App\Models\Asset;
use App\Models\Location;
use App\Models\Property;
use App\Models\WorkOrder;
use App\Services\LeadershipService;
use App\Support\Branding;
use App\Support\CurrentProperty;
use App\Support\DemoSets;
use App\Support\GuestAccess;
use App\Support\Help;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Halaman depan publik situs demo / pra-workshop (konsep AIDA), panduan tanpa login, dan "Masuk sebagai tamu".
 * Aktif bila SIAGA_LANDING / SIAGA_GUEST_MODE = true; selain itu "/" langsung ke halaman masuk.
 */
class LandingController extends Controller
{
    protected function enabled(): bool
    {
        return (bool) config('assetcare.landing') || GuestAccess::enabled();
    }

    public function index()
    {
        if (! $this->enabled()) {
            return redirect('/admin');
        }

        $datasets = $this->datasets();

        return view('landing.index', ['datasets' => $datasets, 'guest' => GuestAccess::enabled(), 'guides' => Help::index(), 'showcase' => $this->showcase($datasets)]);
    }

    /** angka & grafik nyata dari data demo pertama (mis. GPCH) untuk bagian "Lihat angkanya"; disimpan 10 menit */
    protected function showcase(array $datasets): ?array
    {
        $p = $datasets[0]['property'] ?? null;
        if (! $p) {
            return null;
        }

        return Cache::remember('landing:showcase:'.$p->id, 600, function () use ($p, $datasets) {
            $cp = app(CurrentProperty::class);
            $prev = $cp->get();
            $cp->set($p);
            try {
                $d = (new LeadershipService)->data();
            } finally {
                $cp->set($prev);
            }

            return ['title' => $datasets[0]['title'], 'code' => $p->code, 'key' => $datasets[0]['key'], 'kpi' => $d['kpi'],
                'cond' => $d['condition_by_building'], 'weekly' => $d['weekly'], 'cost' => $d['cost_monthly'], 'sla' => $d['sla_priority'], 'survey' => $d['survey']];
        });
    }

    /** data demo yang tersedia: urutan DemoSets, hanya yang sudah dipasang & punya akun tamu */
    public function datasets(): array
    {
        $ready = GuestAccess::datasets()->keyBy(fn ($x) => $x['property']->code);
        $out = [];
        foreach (DemoSets::all() as $s) {
            $p = $ready[$s['code']]['property'] ?? null;
            if (! $p) {
                continue;
            }
            $out[] = $s + ['property' => $p, 'stats' => [
                'aset' => Asset::withoutGlobalScopes()->where('property_id', $p->id)->count(),
                'lokasi' => Location::withoutGlobalScopes()->where('property_id', $p->id)->whereNull('parent_id')->count(),
                'ruang' => Location::withoutGlobalScopes()->where('property_id', $p->id)->where('location_type', 'room')->count(),
                'wo' => WorkOrder::withoutGlobalScopes()->where('property_id', $p->id)->count(),
            ]];
        }
        // data demo lain (mis. dipasang manual) tetap ditampilkan di akhir
        foreach ($ready as $code => $x) {
            if (! collect($out)->contains(fn ($o) => $o['code'] === $code)) {
                $out[] = ['key' => Str::slug($code), 'code' => $code, 'title' => $x['property']->name, 'sector' => $x['property']->city ?? '', 'summary' => '', 'source' => '', 'property' => $x['property'], 'stats' => null];
            }
        }

        return $out;
    }

    public function guideIndex()
    {
        abort_unless($this->enabled(), 404);

        return view('landing.guide', ['guides' => Help::index(), 'g' => null, 'key' => null]);
    }

    public function guide(string $key)
    {
        abort_unless($this->enabled() && Help::has($key), 404);

        return view('landing.guide', ['guides' => Help::index(), 'g' => Help::get($key), 'key' => $key]);
    }

    public function guestLogin(Request $request)
    {
        abort_unless(GuestAccess::enabled(), 404);
        $code = (string) $request->input('dataset');
        $p = Property::where('code', $code)->where('is_active', true)->first();
        $user = $p ? GuestAccess::datasets()->first(fn ($x) => $x['property']->id === $p->id)['user'] ?? null : null;
        if (! $user) {
            return redirect()->route('landing', ['#demo'])->with('landing_error', 'Pilih salah satu data demo terlebih dahulu.');
        }
        Auth::guard('web')->login($user);
        app(CurrentProperty::class)->set($p);
        Branding::flush();
        $request->session()->regenerate();
        $request->session()->put('guest_dataset', $p->code);

        return redirect()->to(DasborPimpinan::getUrl());
    }
}
