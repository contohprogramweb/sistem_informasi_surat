<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use App\Models\Disposisi;
use App\Models\Unit;
use App\Models\KlasifikasiArsip;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RekapDisposisiExport;
use App\Exports\ArsipJatuhTempoExport;
use App\Exports\AuditTrailExport;

class ReportController extends Controller
{
    /**
     * Display main report menu
     */
    public function index()
    {
        return view('reports.index');
    }

    /**
     * Buku Agenda - Surat Masuk
     */
    public function bukuAgendaMasuk(Request $request)
    {
        $periode = $request->get('periode', now()->format('Y-m'));
        $groupBy = $request->get('group_by', 'bulan'); // bulan, klasifikasi
        
        $query = SuratMasuk::with(['klasifikasi', 'sifat', 'unitTujuan']);
        
        if ($periode) {
            $parts = explode('-', $periode);
            if (count($parts) === 2) {
                $year = $parts[0];
                $month = $parts[1];
                $query->whereYear('tanggal_terima', $year)
                      ->whereMonth('tanggal_terima', $month);
            } else {
                $query->whereYear('tanggal_terima', $periode);
            }
        }

        $suratMasuk = $query->orderBy('tanggal_terima', 'desc')
                           ->orderBy('agenda', 'desc')
                           ->paginate(20);

        // Grouping stats
        $stats = [];
        if ($groupBy === 'bulan') {
            $stats = SuratMasuk::selectRaw('YEAR(tanggal_terima) as year, MONTH(tanggal_terima) as month, COUNT(*) as total')
                ->whereYear('tanggal_terima', date('Y', strtotime($periode)))
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get();
        }

        return view('reports.buku-agenda.masuk', compact('suratMasuk', 'stats', 'periode', 'groupBy'));
    }

    /**
     * Buku Agenda - Surat Keluar
     */
    public function bukuAgendaKeluar(Request $request)
    {
        $periode = $request->get('periode', now()->format('Y-m'));
        $groupBy = $request->get('group_by', 'status'); // status, unit
        
        $query = SuratKeluar::with(['unitPembuat', 'klasifikasi', 'sifat']);
        
        if ($periode) {
            $parts = explode('-', $periode);
            if (count($parts) === 2) {
                $year = $parts[0];
                $month = $parts[1];
                $query->whereYear('created_at', $year)
                      ->whereMonth('created_at', $month);
            } else {
                $query->whereYear('created_at', $periode);
            }
        }

        $suratKeluar = $query->orderBy('created_at', 'desc')
                            ->orderBy('nomor_surat_final', 'desc')
                            ->paginate(20);

        // Grouping stats
        $stats = [];
        if ($groupBy === 'status') {
            $stats = SuratKeluar::selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->get();
        }

        return view('reports.buku-agenda.keluar', compact('suratKeluar', 'stats', 'periode', 'groupBy'));
    }

    /**
     * Export Buku Agenda to PDF
     */
    public function exportBukuAgenda(Request $request)
    {
        $type = $request->get('type', 'masuk'); // masuk or keluar
        $periode = $request->get('periode', now()->format('Y-m'));
        
        $query = $type === 'masuk' ? SuratMasuk::query() : SuratKeluar::query();
        
        if ($periode) {
            $parts = explode('-', $periode);
            if (count($parts) === 2) {
                $year = $parts[0];
                $month = $parts[1];
                if ($type === 'masuk') {
                    $query->whereYear('tanggal_terima', $year)
                          ->whereMonth('tanggal_terima', $month);
                } else {
                    $query->whereYear('created_at', $year)
                          ->whereMonth('created_at', $month);
                }
            }
        }

        $data = $query->with(['klasifikasi', 'sifat', 'unitTujuan'])
                     ->orderBy($type === 'masuk' ? 'tanggal_terima' : 'created_at', 'desc')
                     ->get();

        $pdf = Pdf::loadView('reports.pdf.buku-agenda', [
            'data' => $data,
            'type' => $type,
            'periode' => $periode,
            'instansi' => config('app.name'),
            'generatedAt' => now()->format('d F Y H:i:s')
        ]);

        $filename = "buku-agenda-{$type}-{$periode}.pdf";
        return $pdf->download($filename);
    }

    /**
     * Rekap Disposisi
     */
    public function rekapDisposisi(Request $request)
    {
        $unitId = $request->get('unit_id');
        $tanggalMulai = $request->get('tanggal_mulai', now()->startOfMonth()->format('Y-m-d'));
        $tanggalSampai = $request->get('tanggal_sampai', now()->endOfMonth()->format('Y-m-d'));

        $query = Disposisi::with(['suratMasuk', 'dariUser', 'keUser.unit'])
            ->whereBetween('created_at', [$tanggalMulai, $tanggalSampai]);

        if ($unitId) {
            $query->whereHas('keUser', function($q) use ($unitId) {
                $q->where('unit_id', $unitId);
            });
        }

        $disposisi = $query->orderBy('created_at', 'desc')->paginate(20);

        // Summary per unit
        $summary = DB::table('disposisi')
            ->join('users', 'disposisi.ke_user_id', '=', 'users.id')
            ->leftJoin('units', 'users.unit_id', '=', 'units.id')
            ->select(
                'units.id as unit_id',
                'units.nama_unit',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN disposisi.status = 'Selesai' THEN 1 ELSE 0 END) as selesai"),
                DB::raw("SUM(CASE WHEN disposisi.status != 'Selesai' THEN 1 ELSE 0 END) as belum_selesai"),
                DB::raw("SUM(CASE WHEN disposisi.batas_waktu < NOW() AND disposisi.status != 'Selesai' THEN 1 ELSE 0 END) as overdue")
            )
            ->whereBetween('disposisi.created_at', [$tanggalMulai, $tanggalSampai])
            ->groupBy('units.id', 'units.nama_unit')
            ->get();

        $units = Unit::orderBy('nama_unit')->get();

        return view('reports.rekap-disposisi.index', compact('disposisi', 'summary', 'units', 'unitId', 'tanggalMulai', 'tanggalSampai'));
    }

    /**
     * Export Rekap Disposisi to Excel
     */
    public function exportRekapDisposisi(Request $request)
    {
        $unitId = $request->get('unit_id');
        $tanggalMulai = $request->get('tanggal_mulai', now()->startOfMonth()->format('Y-m-d'));
        $tanggalSampai = $request->get('tanggal_sampai', now()->endOfMonth()->format('Y-m-d'));

        $query = Disposisi::with(['suratMasuk', 'dariUser', 'keUser.unit'])
            ->whereBetween('created_at', [$tanggalMulai, $tanggalSampai]);

        if ($unitId) {
            $query->whereHas('keUser', function($q) use ($unitId) {
                $q->where('unit_id', $unitId);
            });
        }

        $data = $query->orderBy('created_at', 'desc')->get();

        return Excel::download(new RekapDisposisiExport($data), "rekap-disposisi-{$tanggalMulai}-{$tanggalSampai}.xlsx");
    }

    /**
     * Arsip Jatuh Tempo
     */
    public function arsipJatuhTempo(Request $request)
    {
        $retensiStatus = $request->get('retensi_status', 'aktif'); // aktif, inaktif
        $bulanDepan = $request->get('bulan_depan', 3); // X bulan ke depan

        $query = SuratMasuk::whereNotNull('tanggal_jatuh_aktif');

        if ($retensiStatus === 'aktif') {
            $query->where('status_arsip', 'aktif')
                  ->whereBetween('tanggal_jatuh_aktif', [now(), now()->addMonths($bulanDepan)]);
        } elseif ($retensiStatus === 'inaktif') {
            $query->where('status_arsip', 'inaktif')
                  ->whereBetween('tanggal_jatuh_inaktif', [now(), now()->addMonths($bulanDepan)]);
        }

        $arsipJatuhTempo = $query->with(['klasifikasi'])
                                  ->orderBy('tanggal_jatuh_aktif')
                                  ->paginate(20);

        // Stats
        $stats = [
            'aktif_3_bulan' => SuratMasuk::where('status_arsip', 'aktif')
                ->whereBetween('tanggal_jatuh_aktif', [now(), now()->addMonths(3)])
                ->count(),
            'inaktif_3_bulan' => SuratMasuk::where('status_arsip', 'inaktif')
                ->whereBetween('tanggal_jatuh_inaktif', [now(), now()->addMonths(3)])
                ->count(),
        ];

        return view('reports.arsip-jatuh-tempo.index', compact('arsipJatuhTempo', 'stats', 'retensiStatus', 'bulanDepan'));
    }

    /**
     * Export Arsip Jatuh Tempo to Excel
     */
    public function exportArsipJatuhTempo(Request $request)
    {
        $retensiStatus = $request->get('retensi_status', 'aktif');
        $bulanDepan = $request->get('bulan_depan', 3);

        $query = SuratMasuk::whereNotNull('tanggal_jatuh_aktif');

        if ($retensiStatus === 'aktif') {
            $query->where('status_arsip', 'aktif')
                  ->whereBetween('tanggal_jatuh_aktif', [now(), now()->addMonths($bulanDepan)]);
        } elseif ($retensiStatus === 'inaktif') {
            $query->where('status_arsip', 'inaktif')
                  ->whereBetween('tanggal_jatuh_inaktif', [now(), now()->addMonths($bulanDepan)]);
        }

        $data = $query->with(['klasifikasi'])->orderBy('tanggal_jatuh_aktif')->get();

        return Excel::download(new ArsipJatuhTempoExport($data), "arsip-jatuh-tempo-{$retensiStatus}.xlsx");
    }

    /**
     * Audit Trail Log
     */
    public function auditTrail(Request $request)
    {
        // Only Admin TU can access
        if (!auth()->user()->hasRole(['admin', 'admin_tu'])) {
            abort(403, 'Unauthorized access. Only Admin TU can view audit logs.');
        }

        $userId = $request->get('user_id');
        $entity = $request->get('entity');
        $action = $request->get('action');
        $tanggalMulai = $request->get('tanggal_mulai', now()->startOfMonth()->format('Y-m-d'));
        $tanggalSampai = $request->get('tanggal_sampai', now()->format('Y-m-d'));

        $query = AuditLog::with(['user'])
            ->whereBetween('created_at', [$tanggalMulai, $tanggalSampai]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($entity) {
            $query->where('entity_type', 'like', "%{$entity}%");
        }

        if ($action) {
            $query->where('action', $action);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(50);

        $users = \App\Models\User::orderBy('name')->get();

        return view('reports.audit-trail.index', compact('logs', 'users', 'userId', 'entity', 'action', 'tanggalMulai', 'tanggalSampai'));
    }

    /**
     * Export Audit Trail to Excel/CSV
     */
    public function exportAuditTrail(Request $request)
    {
        $format = $request->get('format', 'excel'); // excel, csv
        $userId = $request->get('user_id');
        $entity = $request->get('entity');
        $action = $request->get('action');
        $tanggalMulai = $request->get('tanggal_mulai', now()->startOfMonth()->format('Y-m-d'));
        $tanggalSampai = $request->get('tanggal_sampai', now()->format('Y-m-d'));

        $query = AuditLog::with(['user'])
            ->whereBetween('created_at', [$tanggalMulai, $tanggalSampai]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($entity) {
            $query->where('entity_type', 'like', "%{$entity}%");
        }

        if ($action) {
            $query->where('action', $action);
        }

        $data = $query->orderBy('created_at', 'desc')->get();

        if ($format === 'csv') {
            return Excel::download(new AuditTrailExport($data, 'csv'), "audit-trail-{$tanggalMulai}-{$tanggalSampai}.csv");
        }

        return Excel::download(new AuditTrailExport($data), "audit-trail-{$tanggalMulai}-{$tanggalSampai}.xlsx");
    }

    /**
     * Dashboard Statistik with Charts
     */
    public function statistikDashboard(Request $request)
    {
        $tahun = $request->get('tahun', now()->year);

        // Surat Masuk/Keluar per Bulan
        $suratMasukPerBulan = SuratMasuk::selectRaw('MONTH(tanggal_terima) as month, COUNT(*) as total')
            ->whereYear('tanggal_terima', $tahun)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $suratKeluarPerBulan = SuratKeluar::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->whereYear('created_at', $tahun)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Disposisi per Status
        $disposisiPerStatus = Disposisi::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->get();

        // Arsip per Klasifikasi
        $arsipPerKlasifikasi = SuratMasuk::join('klasifikasi_arsip', 'surat_masuk.klasifikasi_id', '=', 'klasifikasi_arsip.id')
            ->select('klasifikasi_arsip.nama_klasifikasi', DB::raw('COUNT(*) as total'))
            ->whereNotNull('surat_masuk.klasifikasi_id')
            ->groupBy('klasifikasi_arsip.id', 'klasifikasi_arsip.nama_klasifikasi')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Summary Cards
        $summary = [
            'surat_masuk_total' => SuratMasuk::whereYear('tanggal_terima', $tahun)->count(),
            'surat_keluar_total' => SuratKeluar::whereYear('created_at', $tahun)->count(),
            'disposisi_total' => Disposisi::whereYear('created_at', $tahun)->count(),
            'disposisi_selesai' => Disposisi::whereYear('created_at', $tahun)->where('status', 'Selesai')->count(),
            'arsip_aktif' => SuratMasuk::where('status_arsip', 'aktif')->count(),
            'arsip_jatuh_tempo' => SuratMasuk::where('status_arsip', 'aktif')
                ->whereBetween('tanggal_jatuh_aktif', [now(), now()->addMonths(3)])
                ->count(),
        ];

        return view('reports.statistik.dashboard', compact(
            'suratMasukPerBulan',
            'suratKeluarPerBulan',
            'disposisiPerStatus',
            'arsipPerKlasifikasi',
            'summary',
            'tahun'
        ));
    }
}
