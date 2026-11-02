<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetLabel;
use App\Models\AssetQuantityGroup;
use App\Models\LabelPrintBatch;
use App\Models\Attachment;
use App\Models\Location;
use App\Models\RoomSurvey;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Pendataan aset awal di lapangan (petugas keliling dengan HP).
 * Per kamar: isi kamar menurut template (aset draft hasil generate) → tempel & pindai label → kondisi/foto →
 * tandai "tidak ada" atau tambah aset di luar template → selesai kamar.
 * Mendukung dua cara label: label yang sudah dicetak per kamar, atau stok label kosong yang dihubungkan di tempat.
 */
class SurveyService
{
    public function __construct(
        protected QrResolver $resolver,
        protected InstallationService $installation,
        protected LabelService $labels,
        protected AssetService $assets,
    ) {}

    protected function authorize(User $user): void
    {
        if (! $user->hasPermission('survey.run')) {
            throw WorkflowException::forbidden('Hanya petugas pendataan yang dapat melakukan aksi ini.');
        }
    }

    /** Aset yang seharusnya ada di ruang ini, urut sesuai template (jenis, posisi). */
    public function items(Location $room): Collection
    {
        return Asset::where('location_id', $room->id)
            ->whereNotIn('lifecycle_status', ['disposed', 'lost', 'retired'])
            ->with(['activeLabel.latestInstallation', 'category', 'slotAssignment.slot'])
            ->get()
            ->sortBy(fn (Asset $a) => implode('|', [
                $a->slotAssignment ? '0' : '1',
                str_pad((string) ($a->slotAssignment?->slot?->item_key ?? $a->category?->code ?? ''), 40),
                str_pad((string) ($a->slotAssignment?->slot?->unit_position ?? 0), 4, '0', STR_PAD_LEFT),
                $a->asset_code,
            ]))->values();
    }

    /** Status satu item untuk checklist. */
    public function itemState(Asset $a): string
    {
        if ($a->verification_status === 'discrepancy') {
            return 'missing';
        }
        $installed = $a->activeLabel?->currentInstallationStatus() === 'installed';
        if ($a->verification_status === 'verified' && $installed) {
            return 'done';
        }
        if ($installed) {
            return 'labeled';   // label tertempel, kondisi belum dicatat
        }
        if ($a->verification_status === 'verified') {
            return 'checked';   // kondisi dicatat, label belum dipindai
        }

        return 'todo';
    }

    public function start(Location $room, User $user): RoomSurvey
    {
        $this->authorize($user);
        $s = RoomSurvey::firstOrNew(['location_id' => $room->id]);
        if (! $s->exists || $s->status === 'done') {
            $s->fill(['property_id' => $room->property_id, 'status' => 'in_progress', 'surveyor_id' => $user->id, 'started_at' => $s->started_at ?? now(), 'finished_at' => null]);
            $s->save();
        }

        return $s;
    }

    /**
     * Pindai label di kamar.
     * @return array{level:string, message:string, asset_id?:int, blank_label_id?:int}
     */
    public function scan(Location $room, string $input, User $user, ?int $forAssetId = null): array
    {
        $this->authorize($user);
        $this->start($room, $user);
        $r = $this->resolver->resolve(mb_substr($input, 0, 500));

        if ($r['type'] === 'blank') {
            if (! $forAssetId) {
                return ['level' => 'info', 'message' => 'Label stok '.$r['label']->stock_code.' terbaca. Pilih aset yang ditempeli label ini.', 'blank_label_id' => $r['label']->id];
            }
            $asset = Asset::where('location_id', $room->id)->findOrFail($forAssetId);

            return $this->attachBlank($room, $r['label'], $asset, $user);
        }
        if ($r['type'] !== 'asset') {
            return ['level' => 'danger', 'message' => $r['message'] ?? 'Label tidak dapat dipakai untuk pendataan.'];
        }
        if ($forAssetId && (int) $r['asset']->id !== $forAssetId) {
            $want = Asset::find($forAssetId);

            return ['level' => 'warning', 'message' => 'Label ini milik '.$r['asset']->asset_code.' ('.$r['asset']->name.'), bukan '.($want?->asset_code ?? 'aset yang dipilih').'. Periksa apakah label tertukar.'];
        }
        $res = $this->installation->confirm($room, $r['label']->token(), $user);
        $res['asset_id'] = $r['asset']->id;

        return $res;
    }

    public function attachBlank(Location $room, AssetLabel $blank, Asset $asset, User $user): array
    {
        $this->authorize($user);
        $label = $this->labels->bindBlank($blank, $asset, $user);
        $res = $this->installation->confirm($room, $label->token(), $user);
        $res['message'] = 'Label stok '.$label->stock_code.' dihubungkan. '.$res['message'];
        $res['asset_id'] = $asset->id;

        return $res;
    }

    /** Catat hasil pengecekan fisik: ada (kondisi, merek/serial, foto) atau tidak ada. */
    public function record(Asset $asset, bool $found, ?string $condition, array $details, User $user, ?UploadedFile $photo = null): Asset
    {
        $this->authorize($user);
        if ($found && ! array_key_exists((string) $condition, Asset::CONDITIONS)) {
            throw WorkflowException::invalid('Pilih kondisi A–E.');
        }
        if (! $found && blank($details['note'] ?? null)) {
            $details['note'] = 'Tidak ditemukan saat pendataan';
        }

        return DB::transaction(function () use ($asset, $found, $condition, $details, $user, $photo) {
            $this->start($asset->location, $user);
            $asset = $this->assets->verify($asset, [
                'found' => $found, 'condition_grade' => $found ? $condition : null, 'note' => $details['note'] ?? null,
                'brand' => $details['brand'] ?? null, 'model' => $details['model'] ?? null, 'serial_number' => $details['serial_number'] ?? null,
            ], $user);
            if ($photo) {
                $this->storePhoto($asset, $photo, $user);
            }

            return $asset;
        });
    }

    /** Aset di luar template (tambahan di kamar, atau area umum) dibuat langsung saat pendataan. */
    public function addExtra(Location $room, array $d, User $user, ?AssetLabel $blank = null, ?UploadedFile $photo = null): Asset
    {
        $this->authorize($user);
        if (blank($d['name'] ?? null) || blank($d['category_id'] ?? null)) {
            throw WorkflowException::invalid('Nama & kategori aset wajib diisi.');
        }
        if (! array_key_exists((string) ($d['condition_grade'] ?? ''), Asset::CONDITIONS)) {
            throw WorkflowException::invalid('Pilih kondisi A–E.');
        }

        return DB::transaction(function () use ($room, $d, $user, $blank, $photo) {
            $this->start($room, $user);
            $asset = new Asset([
                'property_id' => $room->property_id, 'location_id' => $room->id, 'category_id' => $d['category_id'],
                'name' => mb_substr($d['name'], 0, 200), 'brand' => $d['brand'] ?? null, 'model' => $d['model'] ?? null,
                'serial_number' => $d['serial_number'] ?? null, 'condition_grade' => $d['condition_grade'],
                'department_id' => $d['department_id'] ?? null, 'ownership' => 'milik',
                'operational_status' => 'serviceable', 'lifecycle_status' => 'draft', 'verification_status' => 'unverified',
                'migration_source' => 'Pendataan lapangan (di luar template)',
            ]);
            $asset->auditReason = 'Ditemukan saat pendataan '.$room->labelText();
            $asset->save();
            $asset = $this->assets->verify($asset, ['found' => true, 'condition_grade' => $d['condition_grade']], $user);
            if ($blank) {
                $this->attachBlank($room, $blank, $asset, $user);
            } else {
                AssetLabel::issueFor($asset); // label dicetak belakangan (Cetak label QR › belum pernah dicetak)
            }
            if ($photo) {
                $this->storePhoto($asset, $photo, $user);
            }

            return $asset->fresh();
        });
    }

    /** Kelompok hitungan di ruang ini (linen, lampu titik, dsb.): cukup dihitung, tanpa label per unit. */
    public function groups(Location $room): Collection
    {
        return AssetQuantityGroup::where('location_id', $room->id)->where('status', '!=', 'retired')
            ->with('category')->orderBy('category_id')->orderBy('name')->get();
    }

    public function countGroup(AssetQuantityGroup $group, float $qty, User $user): AssetQuantityGroup
    {
        $this->authorize($user);
        if ($qty < 0 || $qty > 100000) {
            throw WorkflowException::invalid('Jumlah tidak valid.');
        }
        $this->start($group->location, $user);
        $group->recordCount($qty, 'Hitung saat pendataan');

        return $group->fresh();
    }

    /**
     * Cetak label kamar ini langsung ke printer portable.
     * Tanpa $assetIds: semua label aset di ruang ini yang belum pernah dicetak. Dengan $assetIds: cetak (ulang) aset tersebut.
     */
    public function printLabels(Location $room, User $user, string $profile, ?array $assetIds = null): LabelPrintBatch
    {
        $this->authorize($user);
        if (! config('assetcare.label_profiles.'.$profile)) {
            throw WorkflowException::invalid('Ukuran label tidak dikenal.');
        }
        $labels = AssetLabel::where('status', 'active')->whereNotNull('asset_id')
            ->whereHas('asset', fn ($q) => $q->where('location_id', $room->id)->whereNotIn('lifecycle_status', ['disposed', 'lost', 'retired']))
            ->when($assetIds, fn ($q) => $q->whereIn('asset_id', $assetIds))
            ->withCount('printItems')->with('asset.slotAssignment.slot')->get();
        if (! $assetIds) {
            $labels = $labels->where('print_items_count', 0);
        }
        if ($labels->isEmpty()) {
            throw WorkflowException::invalid('Tidak ada label yang perlu dicetak di '.$room->labelText().'.');
        }
        $labels = $labels->sortBy(fn ($l) => str_pad((string) ($l->asset->slotAssignment?->slot?->item_key ?? 'zz'), 40).str_pad((string) ($l->asset->slotAssignment?->slot?->unit_position ?? 0), 4, '0', STR_PAD_LEFT).$l->asset->asset_code)->values();
        $reprint = $labels->contains(fn ($l) => $l->print_items_count > 0);

        return $this->labels->createBatch($labels, $profile, 1, $user, ['pendataan' => $room->id], $reprint ? 'reprint' : 'new', $reprint ? 'Cetak ulang di lapangan ('.$room->labelText().')' : null);
    }

    public function unprintedCount(Location $room): int
    {
        return AssetLabel::where('status', 'active')->whereNotNull('asset_id')
            ->whereHas('asset', fn ($q) => $q->where('location_id', $room->id)->whereNotIn('lifecycle_status', ['disposed', 'lost', 'retired']))
            ->doesntHave('printItems')->count();
    }

    public function finish(Location $room, User $user, ?string $note = null): RoomSurvey
    {
        $this->authorize($user);
        $items = $this->items($room);
        $open = $items->filter(fn ($a) => in_array($this->itemState($a), ['todo', 'labeled'], true));
        if ($open->isNotEmpty()) {
            throw WorkflowException::invalid('Masih ada '.$open->count().' item belum dicek: '.$open->take(3)->pluck('name')->join(', ').($open->count() > 3 ? ', …' : '').'. Catat kondisinya atau tandai "Tidak ada".');
        }
        $uncounted = $this->groups($room)->whereNull('on_hand');
        if ($uncounted->isNotEmpty()) {
            throw WorkflowException::invalid('Masih ada '.$uncounted->count().' item hitungan belum dihitung: '.$uncounted->take(3)->pluck('name')->join(', ').'.');
        }
        $s = $this->start($room, $user);
        $s->update([
            'status' => 'done', 'finished_at' => now(), 'surveyor_id' => $user->id, 'note' => $note,
            'found_count' => $items->where('verification_status', 'verified')->count(),
            'missing_count' => $items->where('verification_status', 'discrepancy')->count(),
            'extra_count' => $items->where('migration_source', 'Pendataan lapangan (di luar template)')->count(),
        ]);

        return $s;
    }

    public function reopen(Location $room, User $user): void
    {
        $this->authorize($user);
        RoomSurvey::where('location_id', $room->id)->update(['status' => 'in_progress', 'finished_at' => null]);
    }

    protected function storePhoto(Asset $asset, UploadedFile $photo, User $user): void
    {
        $path = $photo->store('assets/'.$asset->id, 'local');
        Attachment::create([
            'attachable_type' => Asset::class, 'attachable_id' => $asset->id, 'kind' => 'photo', 'disk' => 'local', 'path' => $path,
            'original_name' => mb_substr($photo->getClientOriginalName(), 0, 200), 'mime' => $photo->getMimeType(), 'size' => $photo->getSize(),
            'checksum' => hash_file('sha256', $photo->getRealPath()), 'uploaded_by' => $user->id,
        ]);
    }

    /** Rekap progres: per lantai & per petugas. */
    public function progress(): array
    {
        $rooms = Location::whereIn('location_type', ['room', 'venue', 'area'])->where('is_active', true)->with('parent')->get();
        $surveys = RoomSurvey::with('surveyor')->get()->keyBy('location_id');
        $counts = Asset::whereIn('location_id', $rooms->pluck('id'))->whereNotIn('lifecycle_status', ['disposed', 'lost', 'retired'])
            ->selectRaw("location_id, count(*) as total, sum(case when verification_status = 'verified' then 1 else 0 end) as found, sum(case when verification_status = 'discrepancy' then 1 else 0 end) as missing")
            ->groupBy('location_id')->get()->keyBy('location_id');

        $floors = $rooms->filter(fn ($r) => $counts->has($r->id) || $surveys->has($r->id))
            ->groupBy(fn ($r) => $r->parent?->full_path ?: $r->parent?->name ?: istilah('Tanpa lantai'))
            ->map(function ($rs, $floor) use ($surveys, $counts) {
                $c = $rs->map(fn ($r) => $counts->get($r->id));

                return [
                    'floor' => $floor,
                    'rooms' => $rs->count(),
                    'done' => $rs->filter(fn ($r) => $surveys->get($r->id)?->status === 'done')->count(),
                    'in_progress' => $rs->filter(fn ($r) => $surveys->get($r->id)?->status === 'in_progress')->count(),
                    'items' => (int) $c->sum('total'),
                    'found' => (int) $c->sum('found'),
                    'missing' => (int) $c->sum('missing'),
                ];
            })->sortKeys()->values();

        $people = RoomSurvey::where('status', 'done')->whereNotNull('surveyor_id')->with('surveyor')->get()
            ->groupBy('surveyor_id')->map(fn ($g) => [
                'name' => $g->first()->surveyor?->name ?? '-',
                'rooms' => $g->count(),
                'today' => $g->filter(fn ($s) => $s->finished_at?->copy()->timezone(\App\Support\Fmt::tz())->isToday())->count(),
                'missing' => (int) $g->sum('missing_count'),
                'extra' => (int) $g->sum('extra_count'),
                'last' => $g->max('finished_at'),
            ])->sortByDesc('rooms')->values();

        return ['floors' => $floors, 'people' => $people];
    }
}
