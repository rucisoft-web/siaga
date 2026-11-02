<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Disposal;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Penghapusan aset: usul → setuju/tolak (pemisahan tugas: penyetuju ≠ pengusul) → selesai.
 * Nilai buku dibekukan saat diusulkan; saat selesai aset menjadi "disposed" (atau "lost") dan keluar dari daftar aset tetap.
 */
class DisposalService
{
    public function propose(Asset $asset, array $d, User $user): Disposal
    {
        if (! $user->hasPermission('disposal.request')) {
            throw WorkflowException::forbidden();
        }
        if (in_array($asset->lifecycle_status, ['disposed'], true)) {
            throw WorkflowException::invalid('Aset sudah dihapus.');
        }
        if (Disposal::where('asset_id', $asset->id)->whereIn('status', ['submitted', 'approved'])->exists()) {
            throw WorkflowException::invalid('Aset ini sudah punya usulan penghapusan yang berjalan.');
        }
        if (! isset(Disposal::METHODS[$d['method'] ?? ''])) {
            throw WorkflowException::invalid('Pilih cara penghapusan.');
        }
        $v = (new DepreciationService)->forAsset($asset->loadMissing('financial', 'category'), CarbonImmutable::now()->endOfMonth());

        return Disposal::create([
            'asset_id' => $asset->id, 'method' => $d['method'], 'reason' => mb_substr((string) ($d['reason'] ?? ''), 0, 250) ?: 'Tidak disebutkan',
            'justification' => $d['justification'] ?? null, 'proposed_date' => $d['proposed_date'] ?? now()->toDateString(),
            'book_value' => $v['book'] ?? 0, 'acquisition_cost' => $v['cost'] ?? 0, 'accumulated' => $v['accum'] ?? 0,
            'proceeds' => $d['proceeds'] ?? null, 'stock_take_id' => $d['stock_take_id'] ?? null,
            'status' => 'submitted', 'requested_by' => $user->id,
        ]);
    }

    public function decide(Disposal $d, bool $approve, ?string $note, User $user): Disposal
    {
        if (! $user->hasPermission('disposal.approve')) {
            throw WorkflowException::forbidden();
        }
        if ($d->status !== 'submitted') {
            throw WorkflowException::invalid('Usulan tidak dalam status diusulkan.');
        }
        if ($d->requested_by === $user->id && ! $user->hasRole('super-admin')) {
            throw WorkflowException::invalid('Pemisahan tugas: penyetuju tidak boleh sama dengan pengusul.');
        }
        if (! $approve && ! filled($note)) {
            throw WorkflowException::invalid('Tulis alasan penolakan.');
        }
        $d->update(['status' => $approve ? 'approved' : 'rejected', 'approved_by' => $user->id, 'approved_at' => now(), 'decision_note' => $note]);

        return $d;
    }

    public function complete(Disposal $d, array $data, User $user): Disposal
    {
        if (! $user->hasPermission('disposal.request') && ! $user->hasPermission('disposal.approve')) {
            throw WorkflowException::forbidden();
        }
        if ($d->status !== 'approved') {
            throw WorkflowException::invalid('Hanya usulan yang sudah disetujui yang bisa diselesaikan.');
        }

        return DB::transaction(function () use ($d, $data, $user) {
            $d->update(['status' => 'completed', 'completed_by' => $user->id, 'completed_date' => $data['completed_date'] ?? now()->toDateString(),
                'proceeds' => $data['proceeds'] ?? $d->proceeds, 'buyer' => $data['buyer'] ?? $d->buyer, 'document_ref' => $data['document_ref'] ?? $d->document_ref]);
            $a = $d->asset;
            $a->auditReason = 'Penghapusan aset '.$d->number.' ('.Disposal::METHODS[$d->method].')';
            $a->statusSource = ['disposal', $d->id];
            $a->update(['lifecycle_status' => $d->method === 'lost' ? 'lost' : 'disposed', 'operational_status' => 'out_of_service']);

            return $d;
        });
    }

    public function cancel(Disposal $d, User $user): void
    {
        if ($d->requested_by !== $user->id && ! $user->hasPermission('disposal.approve')) {
            throw WorkflowException::forbidden();
        }
        if (! in_array($d->status, ['submitted', 'approved'], true)) {
            throw WorkflowException::invalid('Usulan tidak bisa dibatalkan.');
        }
        $d->update(['status' => 'cancelled']);
    }
}
