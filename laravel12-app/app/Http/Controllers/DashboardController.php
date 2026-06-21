<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use App\Models\Disposisi;
use App\Models\Unit;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Handle dashboard request based on user role
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Determine user role based on permissions or unit position
        $role = $this->determineUserRole($user);
        
        return match($role) {
            'pimpinan' => $this->pimpinanDashboard($request),
            'kabag' => $this->kabagDashboard($request),
            'staff_tu' => $this->staffTuDashboard($request),
            default => $this->defaultDashboard($request),
        };
    }

    /**
     * Determine user role based on permissions/unit
     */
    private function determineUserRole($user): string
    {
        // Check if user has pimpinan permissions
        if ($user->can('disposisi.massal') || $user->can('surat_masuk.disposisi')) {
            return 'pimpinan';
        }
        
        // Check if user is Kabag (Kepala Bagian) - manages a unit
        if ($user->can('disposisi.forward') && $user->can('surat_keluar.review')) {
            return 'kabag';
        }
        
        // Check if user is Staff TU - handles surat masuk/keluar creation
        if ($user->can('surat_masuk.create') || $user->can('arsip.manage')) {
            return 'staff_tu';
        }
        
        return 'default';
    }

    /**
     * Pimpinan Dashboard
     */
    private function pimpinanDashboard(Request $request)
    {
        $user = Auth::user();
        
        // Widget 1: Surat Masuk Perlu Disposisi
        $suratMasukPerluDisposisi = SuratMasuk::where('tidak_perlu_disposisi', false)
            ->whereHas('disposisi', function($q) use ($user) {
                $q->where('ke_user_id', $user->id)
                  ->whereIn('status', ['Belum Dibaca', 'Sudah Dibaca']);
            })
            ->latest('tanggal_terima')
            ->limit(5)
            ->get();
        
        $countSuratPerluDisposisi = SuratMasuk::where('tidak_perlu_disposisi', false)
            ->whereHas('disposisi', function($q) use ($user) {
                $q->where('ke_user_id', $user->id)
                  ->whereIn('status', ['Belum Dibaca', 'Sudah Dibaca']);
            })
            ->count();
        
        // Widget 2: Disposisi Saya yang Berjalan (with overdue indicator)
        $disposisiBerjalan = Disposisi::where('ke_user_id', $user->id)
            ->whereIn('status', ['Belum Dibaca', 'Sudah Dibaca', 'Sedang Ditindaklanjuti'])
            ->orderByRaw("CASE WHEN batas_waktu < NOW() AND status != 'Selesai' THEN 0 ELSE 1 END")
            ->orderBy('batas_waktu', 'asc')
            ->limit(5)
            ->get();
        
        $countDisposisiOverdue = Disposisi::where('ke_user_id', $user->id)
            ->whereIn('status', ['Belum Dibaca', 'Sudah Dibaca', 'Sedang Ditindaklanjuti'])
            ->where('batas_waktu', '<', now())
            ->count();
        
        // Widget 3: Persetujuan Surat Keluar (menunggu approve/ttd)
        $persetujuanSuratKeluar = SuratKeluar::whereIn('status', ['disetujui', 'siap_ttd', 'review'])
            ->whereHas('unitPembuat', function($q) use ($user) {
                // Get units under this pimpinan
                $q->whereHas('users', function($qu) use ($user) {
                    $qu->where('users.id', $user->id);
                });
            })
            ->latest('updated_at')
            ->limit(5)
            ->get();
        
        $countPersetujuanMenunggu = SuratKeluar::whereIn('status', ['disetujui', 'siap_ttd', 'review'])
            ->count();
        
        // Filter cepat data
        $filterPrioritas = $request->get('prioritas');
        $filterBatasHariIni = $request->get('batas_hari_ini');
        $filterOverdue = $request->get('overdue');
        
        return view('dashboard.pimpinan', compact(
            'suratMasukPerluDisposisi',
            'countSuratPerluDisposisi',
            'disposisiBerjalan',
            'countDisposisiOverdue',
            'persetujuanSuratKeluar',
            'countPersetujuanMenunggu',
            'filterPrioritas',
            'filterBatasHariIni',
            'filterOverdue'
        ));
    }

    /**
     * Kabag Dashboard
     */
    private function kabagDashboard(Request $request)
    {
        $user = Auth::user();
        
        // Widget 1: Disposisi Masuk (belum selesai)
        $disposisiMasuk = Disposisi::where('ke_user_id', $user->id)
            ->whereNotIn('status', ['Selesai'])
            ->latest('created_at')
            ->limit(5)
            ->get();
        
        $countDisposisiMasuk = Disposisi::where('ke_user_id', $user->id)
            ->whereNotIn('status', ['Selesai'])
            ->count();
        
        // Widget 2: Disposisi yang Saya Teruskan (status tracking)
        $disposisiDiteruskan = Disposisi::where('dari_user_id', $user->id)
            ->whereNotNull('parent_id')
            ->latest('updated_at')
            ->limit(5)
            ->get();
        
        // Widget 3: Surat Keluar Unit (draft, review)
        $userUnit = Unit::whereHas('users', function($q) use ($user) {
            $q->where('users.id', $user->id);
        })->first();
        
        $suratKeluarUnit = null;
        $draftCount = 0;
        $reviewCount = 0;
        
        if ($userUnit) {
            $suratKeluarUnit = SuratKeluar::where('unit_pembuat_id', $userUnit->id)
                ->whereIn('status', ['draft', 'review'])
                ->latest('updated_at')
                ->limit(5)
                ->get();
            
            $draftCount = SuratKeluar::where('unit_pembuat_id', $userUnit->id)
                ->where('status', 'draft')
                ->count();
            
            $reviewCount = SuratKeluar::where('unit_pembuat_id', $userUnit->id)
                ->where('status', 'review')
                ->count();
        }
        
        return view('dashboard.kabag', compact(
            'disposisiMasuk',
            'countDisposisiMasuk',
            'disposisiDiteruskan',
            'suratKeluarUnit',
            'draftCount',
            'reviewCount',
            'userUnit'
        ));
    }

    /**
     * Staff TU Dashboard
     */
    private function staffTuDashboard(Request $request)
    {
        // Statistik cards
        $suratMasukHariIni = SuratMasuk::whereDate('tanggal_terima', today())->count();
        $suratKeluarHariIni = SuratKeluar::whereDate('created_at', today())->count();
        
        // Arsip jatuh tempo (within 30 days)
        $arsipJatuhTempo = SuratMasuk::whereBetween('tanggal_jatuh_aktif', [now(), now()->addDays(30)])
            ->orWhereBetween('tanggal_jatuh_inaktif', [now(), now()->addDays(30)])
            ->count();
        
        // Disposisi terbuka
        $disposisiTerbuka = Disposisi::whereIn('status', ['Belum Dibaca', 'Sudah Dibaca', 'Sedang Ditindaklanjuti'])
            ->count();
        
        // Chart data: surat masuk/keluar per bulan (last 6 months)
        $chartData = $this->getChartData();
        
        return view('dashboard.staff-tu', compact(
            'suratMasukHariIni',
            'suratKeluarHariIni',
            'arsipJatuhTempo',
            'disposisiTerbuka',
            'chartData'
        ));
    }

    /**
     * Default Dashboard
     */
    private function defaultDashboard(Request $request)
    {
        return view('dashboard');
    }

    /**
     * Get chart data for last 6 months
     */
    private function getChartData(): array
    {
        $months = [];
        $masukData = [];
        $keluarData = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthLabel = $month->format('M Y');
            $monthNum = $month->month;
            $year = $month->year;
            
            $months[] = $monthLabel;
            
            $masukCount = SuratMasuk::whereMonth('tanggal_terima', $monthNum)
                ->whereYear('tanggal_terima', $year)
                ->count();
            
            $keluarCount = SuratKeluar::whereMonth('created_at', $monthNum)
                ->whereYear('created_at', $year)
                ->count();
            
            $masukData[] = $masukCount;
            $keluarData[] = $keluarCount;
        }
        
        return [
            'labels' => $months,
            'masuk' => $masukData,
            'keluar' => $keluarData,
        ];
    }

    /**
     * API endpoint for real-time notification badge update
     */
    public function getNotificationCounts()
    {
        $user = Auth::user();
        $role = $this->determineUserRole($user);
        
        $counts = [];
        
        if ($role === 'pimpinan') {
            $counts['surat_perlu_disposisi'] = SuratMasuk::where('tidak_perlu_disposisi', false)
                ->whereHas('disposisi', function($q) use ($user) {
                    $q->where('ke_user_id', $user->id)
                      ->whereIn('status', ['Belum Dibaca', 'Sudah Dibaca']);
                })
                ->count();
            
            $counts['disposisi_overdue'] = Disposisi::where('ke_user_id', $user->id)
                ->whereIn('status', ['Belum Dibaca', 'Sudah Dibaca', 'Sedang Ditindaklanjuti'])
                ->where('batas_waktu', '<', now())
                ->count();
        } elseif ($role === 'kabag') {
            $counts['disposisi_masuk'] = Disposisi::where('ke_user_id', $user->id)
                ->whereNotIn('status', ['Selesai'])
                ->count();
        }
        
        return response()->json($counts);
    }
}
