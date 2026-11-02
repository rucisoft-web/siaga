<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Support\Branding;
use App\Support\CurrentProperty;
use App\Support\Terms;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TermsTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected function useType(string $type): void
    {
        app(CurrentProperty::class)->get()->update(['business_type' => $type]);
        Branding::flush();
    }

    public function test_terms_follow_business_type(): void
    {
        $this->assertSame('Blokir kamar', Terms::t('Blokir kamar'));
        $this->assertSame('Tutup outlet', Terms::t('Blokir kamar', 'retail'));
        $this->assertSame('Rayon & area', Terms::t('Lantai & area', 'retail'));
        $this->assertSame('Kamar mandi', Terms::t('Kamar mandi', 'retail'), 'kamar mandi tetap kamar mandi');
        $this->assertSame('Kelas perawatan', Terms::t('Tipe kamar', 'hospital'));
        $this->assertSame('Tutup ruang', Terms::t('Blokir kamar', 'general'));

        $room = Location::where('location_type', 'room')->first();
        $this->useType('hotel');
        $this->assertStringStartsWith('Kamar ', $room->labelText());
        $this->useType('retail');
        $this->assertSame($room->name, $room->labelText(), 'label outlet memakai nama outlet, bukan "Kamar"');
        $this->assertSame('Outlet', Location::typeLabels()['room']);
        $this->assertSame('Tutup outlet', istilah('Blokir kamar'));
    }
}
