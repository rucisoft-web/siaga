<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Support\Branding;
use App\Support\CurrentProperty;
use Illuminate\Http\Request;

/** Direksi grup / Super Admin berpindah perusahaan aktif (disimpan di sesi, tercatat di log audit). */
class PropertySwitchController extends Controller
{
    public function __invoke(Request $request)
    {
        abort_unless(CurrentProperty::canSwitch($request->user()), 403);
        $p = Property::where('is_active', true)->findOrFail($request->integer('property_id'));
        $request->session()->put('active_property_id', $p->id);
        Branding::flush();
        if (class_exists(\App\Models\AuditLog::class)) {
            try {
                \App\Models\AuditLog::create(['property_id' => $p->id, 'user_id' => $request->user()->id, 'event' => 'switch_property', 'auditable_label' => $p->name, 'ip_address' => $request->ip(), 'url' => $request->path(),
                    'auditable_type' => Property::class, 'auditable_id' => $p->id, 'reason' => 'Pindah perusahaan aktif ke '.$p->code]);
            } catch (\Throwable) {
            }
        }
        $back = (string) $request->input('back', '');

        return redirect()->to(str_starts_with($back, '/admin') ? $back : '/admin');
    }
}
