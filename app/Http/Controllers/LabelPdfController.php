<?php

namespace App\Http\Controllers;

use App\Models\LabelPrintBatch;
use App\Services\LabelService;
use Illuminate\Http\Request;

class LabelPdfController extends Controller
{
    public function show(Request $request, LabelPrintBatch $batch, LabelService $labels)
    {
        $this->authorizeBatch($request, $batch);
        $guides = $request->boolean('guides');
        $pdf = $labels->pdf($batch, $guides);
        if (! $guides) {
            $labels->markDownloaded($batch);
        }

        return $pdf->stream($batch->number.($guides ? '-uji' : '').'.pdf');
    }

    /**
     * Cetak langsung dari browser (Mobile Terminal / HP → printer label Bluetooth lewat layanan cetak Android).
     * Halaman berukuran persis satu label per halaman dan membuka dialog cetak otomatis.
     */
    public function print(Request $request, LabelPrintBatch $batch, LabelService $labels)
    {
        $this->authorizeBatch($request, $batch);
        $labels->markDownloaded($batch);

        return response($labels->printHtml($batch, $request->boolean('auto', true)));
    }

    protected function authorizeBatch(Request $request, LabelPrintBatch $batch): void
    {
        $u = $request->user();
        $field = isset($batch->filters['pendataan']) && $u->hasPermission('survey.run') && (int) $batch->operator_id === (int) $u->id;
        abort_unless($u->hasPermission('label.print') || $field, 403);
    }
}
