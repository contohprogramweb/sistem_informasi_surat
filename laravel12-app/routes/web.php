<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Reports\ReportController;
use App\Http\Controllers\Admin\SystemController;

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('auth');

// Pencarian Global
Route::get('/search', [SearchController::class, 'index'])->name('search.index')->middleware('auth');
Route::get('/search/api', [SearchController::class, 'api'])->name('search.api')->middleware('auth');
Route::get('/search/export', [SearchController::class, 'export'])->name('search.export')->middleware('auth');
Route::get('/search/filter-options', [SearchController::class, 'filterOptions'])->name('search.filters')->middleware('auth');

// Laporan & Statistik
Route::prefix('reports')->name('reports.')->middleware('auth')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');
    
    // Buku Agenda
    Route::get('/buku-agenda/masuk', [ReportController::class, 'bukuAgendaMasuk'])->name('buku-agenda.masuk');
    Route::get('/buku-agenda/keluar', [ReportController::class, 'bukuAgendaKeluar'])->name('buku-agenda.keluar');
    Route::get('/export/buku-agenda', [ReportController::class, 'exportBukuAgenda'])->name('export.buku-agenda');
    
    // Rekap Disposisi
    Route::get('/rekap-disposisi', [ReportController::class, 'rekapDisposisi'])->name('rekap-disposisi');
    Route::get('/export/rekap-disposisi', [ReportController::class, 'exportRekapDisposisi'])->name('export.rekap-disposisi');
    
    // Arsip Jatuh Tempo
    Route::get('/arsip-jatuh-tempo', [ReportController::class, 'arsipJatuhTempo'])->name('arsip-jatuh-tempo');
    Route::get('/export/arsip-jatuh-tempo', [ReportController::class, 'exportArsipJatuhTempo'])->name('export.arsip-jatuh-tempo');
    
    // Audit Trail (dari modul Reports)
    Route::get('/audit-trail', [ReportController::class, 'auditTrail'])->name('audit-trail');
    Route::get('/export/audit-trail', [ReportController::class, 'exportAuditTrail'])->name('export.audit-trail');
    
    // Statistik Dashboard
    Route::get('/statistik', [ReportController::class, 'statistik'])->name('statistik');
});

// Admin System Routes (Audit Trail, Error Logs, Import)
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin,staff_tu'])->group(function () {
    // Audit Trail
    Route::get('/audit-trail', [SystemController::class, 'auditTrail'])->name('audit-trail');
    Route::get('/audit-trail/export', [SystemController::class, 'exportAuditTrail'])->name('audit-trail.export');
    
    // Error Logs
    Route::get('/error-logs', [SystemController::class, 'errorLogs'])->name('error-logs');
    
    // Import Data
    Route::get('/import', [SystemController::class, 'importIndex'])->name('import.index');
    Route::post('/import/preview', [SystemController::class, 'preview'])->name('import.preview');
    Route::post('/import/process', [SystemController::class, 'processImport'])->name('import.process');
    Route::get('/import/history/{id?}', [SystemController::class, 'importHistory'])->name('import.history');
});

// API untuk real-time notification (optional)
Route::get('/api/dashboard/notification-counts', [DashboardController::class, 'notificationCounts'])
    ->name('api.notification-counts')
    ->middleware('auth');

// Home redirect
Route::get('/', function () {
    return redirect()->route('dashboard');
})->middleware('auth');
