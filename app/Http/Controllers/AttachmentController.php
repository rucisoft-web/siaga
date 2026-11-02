<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Attachment;
use App\Models\ServiceRequest;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/** Lampiran privat: hanya bagi pengguna yang boleh melihat objek pemiliknya. */
class AttachmentController extends Controller
{
    public function show(Request $request, Attachment $attachment)
    {
        $u = $request->user();
        $owner = $attachment->attachable;
        $allowed = match (true) {
            $owner instanceof Asset => $u->hasPermission('asset.view'),
            $owner instanceof WorkOrder => WorkOrder::visibleTo($u)->whereKey($owner->id)->exists() && $u->hasPermission('wo.view'),
            $owner instanceof ServiceRequest => ServiceRequest::visibleTo($u)->whereKey($owner->id)->exists(),
            default => false,
        };
        if ($attachment->visibility === 'financial' && ! $u->canSeeFinancials()) {
            $allowed = false;
        }
        abort_unless($allowed, 403);
        $disk = Storage::disk($attachment->disk ?: 'local');
        abort_unless($disk->exists($attachment->path), 404);

        return $disk->response($attachment->path, $attachment->original_name ?: basename($attachment->path), [
            'Content-Type' => $attachment->mime ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=0',
        ], 'inline');
    }
}
