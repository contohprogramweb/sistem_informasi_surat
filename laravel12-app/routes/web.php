<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuratMasukController;
use App\Http\Controllers\SuratKeluarController;
use App\Http\Controllers\DisposisiController;
use App\Http\Controllers\ArsipRetensiController;
use App\Http\Controllers\TteSignatureController;
use App\Http\Controllers\Master\UnitController;
use App\Http\Controllers\Master\KlasifikasiArsipController;
use App\Http\Controllers\Master\SifatSuratController;
use App\Http\Controllers\Master\TemplateDisposisiController;
use App\Http\Controllers\Reports\ReportController;
use App\Http\Controllers\Admin\SystemController;

// ============================================
// AUTHENTICATION ROUTES
// ============================================
Route::middleware('guest')->group(function () {
    Route::get('login', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'create'])
        ->name('login');
    Route::post('login', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store']);
    Route::get('forgot-password', [App\Http\Controllers\Auth\PasswordResetLinkController::class, 'create'])
        ->name('password.request');
    Route::post('forgot-password', [App\Http\Controllers\Auth\PasswordResetLinkController::class, 'store'])
        ->name('password.email');
    Route::get('reset-password/{token}', [App\Http\Controllers\Auth\NewPasswordController::class, 'create'])
        ->name('password.reset');
    Route::post('reset-password', [App\Http\Controllers\Auth\NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
    
    // Profile
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ============================================
// DASHBOARD
// ============================================
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('auth');
Route::get('/api/dashboard/notification-counts', [DashboardController::class, 'getNotificationCounts'])
    ->name('api.notification-counts')
    ->middleware('auth');

// ============================================
// NOTIFICATIONS
// ============================================
Route::prefix('notifications')->name('notifications.')->middleware('auth')->group(function () {
    Route::get('/', [NotificationController::class, 'getAll'])->name('all');
    Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
    Route::post('/mark-as-read/{id}', [NotificationController::class, 'markAsRead'])->name('mark-as-read');
    Route::post('/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-as-read');
    Route::post('/read-receipt', [NotificationController::class, 'recordReadReceipt'])->name('read-receipt');
});

// ============================================
// SURAT MASUK
// ============================================
Route::prefix('surat-masuk')->name('surat-masuk.')->middleware('auth')->group(function () {
    Route::get('/', [SuratMasukController::class, 'index'])->name('index');
    Route::get('/create', [SuratMasukController::class, 'create'])->name('create');
    Route::post('/', [SuratMasukController::class, 'store'])->name('store');
    Route::get('/{suratMasuk}', [SuratMasukController::class, 'show'])->name('show');
    Route::get('/{suratMasuk}/edit', [SuratMasukController::class, 'edit'])->name('edit');
    Route::put('/{suratMasuk}', [SuratMasukController::class, 'update'])->name('update');
    Route::delete('/{suratMasuk}', [SuratMasukController::class, 'destroy'])->name('destroy');
    Route::post('/{suratMasuk}/archive', [ArsipRetensiController::class, 'archiveSuratMasuk'])->name('archive');
    Route::post('/{suratMasuk}/restore', [SuratMasukController::class, 'restore'])->name('restore');
    Route::get('/{suratMasuk}/disposisi/create', [DisposisiController::class, 'create'])->name('disposisi.create');
    Route::post('/{suratMasuk}/disposisi', [DisposisiController::class, 'store'])->name('disposisi.store');
    Route::post('/massal-disposisi', [DisposisiController::class, 'massalDisposisi'])->name('disposisi.massal');
});

// ============================================
// SURAT KELUAR
// ============================================
Route::prefix('surat-keluar')->name('surat-keluar.')->middleware('auth')->group(function () {
    Route::get('/', [SuratKeluarController::class, 'index'])->name('index');
    Route::get('/create', [SuratKeluarController::class, 'create'])->name('create');
    Route::post('/', [SuratKeluarController::class, 'store'])->name('store');
    Route::get('/{suratKeluar}', [SuratKeluarController::class, 'show'])->name('show');
    Route::get('/{suratKeluar}/edit', [SuratKeluarController::class, 'edit'])->name('edit');
    Route::put('/{suratKeluar}', [SuratKeluarController::class, 'update'])->name('update');
    Route::delete('/{suratKeluar}', [SuratKeluarController::class, 'destroy'])->name('destroy');
    Route::post('/{suratKeluar}/submit-review', [SuratKeluarController::class, 'submitReview'])->name('submit-review');
    Route::post('/{suratKeluar}/approve', [SuratKeluarController::class, 'approve'])->name('approve');
    Route::post('/{suratKeluar}/reject', [SuratKeluarController::class, 'reject'])->name('reject');
    Route::post('/{suratKeluar}/finalize-number', [SuratKeluarController::class, 'finalizeNumber'])->name('finalize-number');
    Route::post('/{suratKeluar}/send', [SuratKeluarController::class, 'send'])->name('send');
    Route::get('/{suratKeluar}/sign', [TteSignatureController::class, 'signPage'])->name('sign');
    Route::post('/{suratKeluar}/sign', [TteSignatureController::class, 'processSign'])->name('sign.process');
    Route::post('/{suratKeluar}/archive', [ArsipRetensiController::class, 'archiveSuratKeluar'])->name('archive');
});

// ============================================
// DISPOSISI
// ============================================
Route::prefix('disposisi')->name('disposisi.')->middleware('auth')->group(function () {
    Route::get('/saya', [DisposisiController::class, 'saya'])->name('saya');
    Route::get('/{disposisi}', [DisposisiController::class, 'show'])->name('show');
    Route::put('/{disposisi}/status', [DisposisiController::class, 'updateStatus'])->name('update-status');
    Route::put('/{disposisi}/forward', [DisposisiController::class, 'forward'])->name('forward');
    Route::post('/{disposisi}/komentar', [DisposisiController::class, 'addKomentar'])->name('add-komentar');
    Route::post('/{disposisi}/upload-tindak-lanjut', [DisposisiController::class, 'uploadTindakLanjut'])->name('upload-tindak-lanjut');
    Route::get('/template', [TemplateDisposisiController::class, 'index'])->name('template.index');
    Route::post('/template', [TemplateDisposisiController::class, 'store'])->name('template.store');
    Route::delete('/template/{template}', [TemplateDisposisiController::class, 'destroy'])->name('template.destroy');
});

// ============================================
// ARSIP & RETENSI
// ============================================
Route::prefix('arsip')->name('arsip.')->middleware('auth')->group(function () {
    Route::get('/', [ArsipRetensiController::class, 'index'])->name('index');
    Route::get('/jatuh-tempo', [ReportController::class, 'arsipJatuhTempo'])->name('jatuh-tempo');
    Route::get('/{suratMasuk}/detail', [ArsipRetensiController::class, 'showDetail'])->name('show-detail');
    Route::post('/{beritaAcara}/musnahkan', [ArsipRetensiController::class, 'musnahkanArsip'])->name('musnahkan');
});

// ============================================
// PENCARIAN GLOBAL
// ============================================
Route::prefix('search')->name('search.')->middleware('auth')->group(function () {
    Route::get('/', [SearchController::class, 'index'])->name('index');
    Route::get('/api', [SearchController::class, 'api'])->name('api');
    Route::get('/export', [SearchController::class, 'export'])->name('export');
    Route::get('/filter-options', [SearchController::class, 'filterOptions'])->name('filters');
});

// ============================================
// MASTER DATA
// ============================================
Route::prefix('master')->name('master.')->middleware(['auth', 'role:admin,staff_tu'])->group(function () {
    // Unit
    Route::resource('units', UnitController::class)->parameters(['units' => 'unit']);
    
    // Klasifikasi Arsip
    Route::get('/klasifikasi-arsip/tree', [KlasifikasiArsipController::class, 'tree'])->name('klasifikasi-arsip.tree');
    Route::resource('klasifikasi-arsip', KlasifikasiArsipController::class)->parameters(['klasifikasi-arsip' => 'klasifikasi']);
    
    // Sifat Surat
    Route::resource('sifat-surat', SifatSuratController::class)->parameters(['sifat-surat' => 'sifat']);
    
    // Template Disposisi (handled in disposisi routes above)
});

// ============================================
// TTE SIGNATURE MANAGEMENT
// ============================================
Route::prefix('tte')->name('tte.')->middleware('auth')->group(function () {
    Route::get('/upload-signature', [TteSignatureController::class, 'uploadSignaturePage'])->name('upload-signature');
    Route::post('/upload-signature', [TteSignatureController::class, 'uploadSignature'])->name('upload-signature.process');
});

// ============================================
// LAPORAN & STATISTIK
// ============================================
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

// ============================================
// ADMIN SYSTEM ROUTES
// ============================================
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
    
    // Delegasi Management
    Route::get('/delegasi', [SystemController::class, 'delegasiIndex'])->name('delegasi.index');
    Route::post('/delegasi', [SystemController::class, 'delegasiCreate'])->name('delegasi.create');
    Route::put('/delegasi/{delegasi}', [SystemController::class, 'delegasiUpdate'])->name('delegasi.update');
    Route::delete('/delegasi/{delegasi}', [SystemController::class, 'delegasiDelete'])->name('delegasi.delete');
});

// ============================================
// HOME REDIRECT
// ============================================
Route::get('/', function () {
    return redirect()->route('dashboard');
})->middleware('auth');
