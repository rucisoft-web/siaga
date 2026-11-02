<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\LabelPdfController;
use App\Http\Controllers\QrController;
use App\Http\Controllers\ReportExportController;
use Illuminate\Support\Facades\Route;

// Halaman depan (landing AIDA), panduan publik & "Masuk sebagai tamu" — hanya aktif di situs demo (SIAGA_LANDING / SIAGA_GUEST_MODE)
Route::get('/', [\App\Http\Controllers\LandingController::class, 'index'])->name('landing');
Route::get('/panduan', [\App\Http\Controllers\LandingController::class, 'guideIndex'])->name('landing.guides');
Route::get('/panduan/{key}', [\App\Http\Controllers\LandingController::class, 'guide'])->where('key', '[a-z0-9-]+')->name('landing.guide');
Route::post('/demo/masuk', [\App\Http\Controllers\LandingController::class, 'guestLogin'])->middleware('throttle:20,1')->name('landing.guest');

Route::get('/brand/logo/{variant}', [\App\Http\Controllers\BrandLogoController::class, 'show'])
    ->whereIn('variant', ['light', 'dark'])->name('brand.logo');

// URL pada label QR. Tamu diarahkan ke login lalu kembali ke sini; token disimpan hanya dalam bentuk hash.
Route::get('/q/{token}', [QrController::class, 'show'])->where('token', '[A-Za-z0-9_-]{20,64}')
    ->middleware('throttle:60,1')->name('qr.show');

Route::middleware(['auth'])->group(function () {
    Route::get('/labels/{batch}/pdf', [LabelPdfController::class, 'show'])->name('labels.pdf');
    Route::get('/labels/{batch}/print', [LabelPdfController::class, 'print'])->name('labels.print');
    Route::get('/attachments/{attachment}', [AttachmentController::class, 'show'])->name('attachments.show');
    Route::get('/reports/{report}/export', [ReportExportController::class, 'export'])->name('reports.export');
});
