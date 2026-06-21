<?php

namespace App\Providers;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use App\Models\Disposisi;
use App\Models\Delegasi;
use App\Models\Lampiran;
use App\Observers\SuratMasukObserver;
use App\Observers\SuratKeluarObserver;
use App\Observers\DisposisiObserver;
use App\Observers\DelegasiObserver;
use App\Observers\UserObserver;
use App\Observers\LampiranObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Rate limiting untuk login: max 3 percobaan gagal per 15 menit
        RateLimiter::for('login', function (Request $request) {
            return RateLimiter::limit($request->input('email'), 3)->response(function () {
                return response()->json([
                    'message' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam 15 menit.'
                ], 429);
            });
        });

        // Gate untuk cek akses berbasis unit
        // Kabag hanya bisa melihat surat dari unit-nya sendiri
        Gate::define('view-surat-unit', function (User $user, $surat) {
            // Jika user punya permission view.all, izinkan akses
            if ($user->can('surat_masuk.view.all')) {
                return true;
            }

            // Cek berdasarkan tipe surat
            if ($surat instanceof SuratMasuk) {
                return $user->unit_id === $surat->unit_id;
            }

            if ($surat instanceof SuratKeluar) {
                return $user->unit_id === $surat->unit_id;
            }

            return false;
        });

        // Gate untuk disposisi: hanya bisa menerima disposisi jika ditujukan ke user tersebut atau unit-nya
        Gate::define('receive-disposisi', function (User $user, $disposisi) {
            if ($user->hasRole('pimpinan')) {
                return true;
            }
            
            return $disposisi->ke_user_id === $user->id 
                || ($disposisi->ke_user_id === null && $user->unit_id === $disposisi->unit_id);
        });

        // Gate untuk approve surat keluar: hanya pimpinan atau kabag dengan permission
        Gate::define('approve-surat-keluar', function (User $user, $suratKeluar) {
            if ($user->can('surat_keluar.approve')) {
                return true;
            }
            
            // Kabag hanya bisa approve surat dari unit-nya
            if ($user->hasRole('kabag') && $user->can('surat_keluar.review')) {
                return $user->unit_id === $suratKeluar->unit_id;
            }

            return false;
        });

        // Register Observers untuk Audit Trail
        SuratMasuk::observe(SuratMasukObserver::class);
        SuratKeluar::observe(SuratKeluarObserver::class);
        Disposisi::observe(DisposisiObserver::class);
        Delegasi::observe(DelegasiObserver::class);
        User::observe(UserObserver::class);
        Lampiran::observe(LampiranObserver::class);
    }
}
