<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ============================================
// MASTER DATA ROUTES (SIAP-SMK)
// ============================================
use App\Http\Controllers\Master\UnitController;
use App\Http\Controllers\Master\KlasifikasiArsipController;
use App\Http\Controllers\Master\SifatSuratController;
use App\Http\Controllers\Master\TemplateDisposisiController;

Route::middleware(['auth', 'verified'])->prefix('master')->name('master.')->group(function () {
    
    // Unit CRUD
    Route::resource('units', UnitController::class);
    Route::post('units/{unit}/restore', [UnitController::class, 'restore'])->name('units.restore')->withTrashed();
    
    // Klasifikasi Arsip CRUD + Tree
    Route::resource('klasifikasi', KlasifikasiArsipController::class);
    Route::post('klasifikasi/{klasifikasi}/restore', [KlasifikasiArsipController::class, 'restore'])->name('klasifikasi.restore');
    Route::get('klasifikasi-tree-list', [KlasifikasiArsipController::class, 'treeList'])->name('klasifikasi.tree');
    
    // Sifat Surat CRUD
    Route::resource('sifat-surat', SifatSuratController::class);
    Route::post('sifat-surat/{id}/restore', [SifatSuratController::class, 'restore'])->name('sifat-surat.restore');
    
    // Template Disposisi CRUD (hanya milik user sendiri)
    Route::resource('template-disposisi', TemplateDisposisiController::class);
});

// ============================================
// ROUTE SIAP-SMK dengan RBAC dan Permission
// ============================================
// Catatan: Controller untuk fitur SIAP-SMK belum dibuat.
// Route di bawah ini adalah contoh implementasi RBAC yang siap digunakan
// setelah controller-controller terkait dibuat.

Route::middleware(['auth', 'verified'])->group(function () {
    
    // ========================================
    // SURAT MASUK
    // ========================================
    
    // View surat masuk (menggunakan Gate untuk unit-based access)
    Route::get('/surat-masuk', function() {
        return view('surat-masuk.index'); // Buat view nanti
    })->name('surat-masuk.index')
      ->can('surat_masuk.view.unit'); // Minimal permission untuk view
    
    // Detail surat masuk dengan Gate check
    Route::get('/surat-masuk/{id}', function($id) {
        return view('surat-masuk.show', ['id' => $id]); // Buat view nanti
    })->name('surat-masuk.show')
      ->can('view-surat-unit'); // Menggunakan Gate untuk cek unit access
    
    // Create & Store surat masuk
    Route::middleware(['permission:surat_masuk.create'])->group(function () {
        Route::get('/surat-masuk/create', function() {
            return view('surat-masuk.create');
        })->name('surat-masuk.create');
        
        Route::post('/surat-masuk', function() {
            // Implementasi store nanti
            return redirect()->route('surat-masuk.index');
        })->name('surat-masuk.store');
    });
    
    // Edit & Update surat masuk
    Route::middleware(['permission:surat_masuk.edit'])->group(function () {
        Route::get('/surat-masuk/{id}/edit', function($id) {
            return view('surat-masuk.edit', ['id' => $id]);
        })->name('surat-masuk.edit');
        
        Route::put('/surat-masuk/{id}', function($id) {
            // Implementasi update nanti
            return redirect()->route('surat-masuk.index');
        })->name('surat-masuk.update');
        
        Route::delete('/surat-masuk/{id}', function($id) {
            // Implementasi delete nanti
            return redirect()->route('surat-masuk.index');
        })->name('surat-masuk.destroy');
    });

    // ========================================
    // SURAT KELUAR
    // ========================================
    Route::resource('surat-keluar', \App\Http\Controllers\SuratKeluarController::class);
    Route::post('surat-keluar/{suratKeluar}/submit-review', [\App\Http\Controllers\SuratKeluarController::class, 'submitReview'])->name('surat-keluar.submit-review');
    Route::post('surat-keluar/{suratKeluar}/approve', [\App\Http\Controllers\SuratKeluarController::class, 'approve'])->name('surat-keluar.approve')->middleware(['permission:surat_keluar.approve']);
    Route::post('surat-keluar/{suratKeluar}/reject', [\App\Http\Controllers\SuratKeluarController::class, 'reject'])->name('surat-keluar.reject');
    Route::post('surat-keluar/{suratKeluar}/prepare-sign', [\App\Http\Controllers\SuratKeluarController::class, 'prepareSign'])->name('surat-keluar.prepare-sign');
    Route::post('surat-keluar/{suratKeluar}/sign', [\App\Http\Controllers\SuratKeluarController::class, 'sign'])->name('surat-keluar.sign')->middleware(['permission:surat_keluar.ttd']);
    Route::post('surat-keluar/{suratKeluar}/send', [\App\Http\Controllers\SuratKeluarController::class, 'send'])->name('surat-keluar.send');
    Route::post('surat-keluar/{suratKeluar}/transition', [\App\Http\Controllers\SuratKeluarController::class, 'transition'])->name('surat-keluar.transition');

    Route::middleware(['permission:disposisi.create'])->group(function () {
        Route::post('/disposisi', function() {
            // Implementasi store nanti
            return back();
        })->name('disposisi.store');
        
        Route::get('/disposisi/create', function() {
            return view('disposisi.create');
        })->name('disposisi.create');
    });
    
    // Receive disposisi (menggunakan Gate)
    Route::get('/disposisi/saya', function() {
        return view('disposisi.saya');
    })->name('disposisi.saya')
      ->can('disposisi.receive');
    
    // Forward disposisi
    Route::middleware(['permission:disposisi.forward'])->group(function () {
        Route::post('/disposisi/{id}/forward', function($id) {
            // Implementasi forward nanti
            return back();
        })->name('disposisi.forward');
    });
    
    // Disposisi massal (hanya pimpinan)
    Route::middleware(['permission:disposisi.massal'])->group(function () {
        Route::post('/disposisi/massal', function() {
            // Implementasi disposisi massal nanti
            return back();
        })->name('disposisi.massal');
    });
    
    // ========================================
    // MASTER DATA & ADMIN
    // ========================================
    
    // Manage master data
    Route::middleware(['permission:master_data.manage'])->prefix('admin')->name('admin.')->group(function () {
        // Route::resource('units', \App\Http\Controllers\UnitController::class)->except(['show']);
        // Route::resource('klasifikasi', \App\Http\Controllers\KlasifikasiArsipController::class)->except(['show']);
        
        // Placeholder routes (buat controller nanti)
        Route::get('/units', function() { return view('admin.units.index'); })->name('units.index');
        Route::get('/klasifikasi', function() { return view('admin.klasifikasi.index'); })->name('klasifikasi.index');
    });
    
    // Manage users
    Route::middleware(['permission:user.manage'])->prefix('admin')->name('admin.')->group(function () {
        // Route::resource('users', \App\Http\Controllers\UserController::class)->except(['show']);
        
        // Placeholder routes (buat controller nanti)
        Route::get('/users', function() { return view('admin.users.index'); })->name('users.index');
    });
    
    // Laporan
    Route::middleware(['permission:laporan.view'])->get('/laporan', function() {
        return view('laporan.index');
    })->name('laporan.index');
    
    // Arsip manage
    Route::middleware(['permission:arsip.manage'])->prefix('arsip')->name('arsip.')->group(function () {
        Route::get('/', function() {
            return view('arsip.index');
        })->name('index');
        
        Route::post('/retensi', function() {
            // Implementasi retensi nanti
            return back();
        })->name('retensi');
    });
    
    // ========================================
    // FITUR LANJUTAN
    // ========================================
    
    // TTE (Tanda Tangan Elektronik)
    Route::middleware(['permission:tte.execute'])->post('/tte/sign', function() {
        // Implementasi TTE nanti
        return back();
    })->name('tte.sign');
    
    // Import Excel
    Route::middleware(['permission:import.execute'])->post('/import', function() {
        // Implementasi import nanti
        return back();
    })->name('import.execute');
    
    // Audit logs view
    Route::middleware(['permission:audit.view'])->get('/audit-logs', function() {
        return view('audit-logs.index');
    })->name('audit-logs.index');
});

require __DIR__.'/auth.php';
