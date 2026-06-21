<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use App\Models\BeritaAcaraPemusnahan;
use App\Models\ArsipNotification;
use App\Services\ArsipRetensiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ArsipRetensiController extends Controller
{
    protected ArsipRetensiService $service;

    public function __construct(ArsipRetensiService $service)
    {
        $this->service = $service;
    }

    /**
     * Display arsip dashboard
     */
    public function index(Request $request)
    {
        $filter = $request->only(['status_arsip', 'type', 'klasifikasi_id']);
        
        // Get archived surat masuk
        $suratMasukQuery = SuratMasuk::with(['klasifikasi', 'unitTujuan'])
            ->whereIn('status_arsip', ['aktif', 'inaktif', 'dimusnahkan']);
        
        if (isset($filter['status_arsip'])) {
            $suratMasukQuery->where('status_arsip', $filter['status_arsip']);
        }
        
        if (isset($filter['klasifikasi_id'])) {
            $suratMasukQuery->where('klasifikasi_id', $filter['klasifikasi_id']);
        }
        
        $suratMasuk = $suratMasukQuery->orderBy('tanggal_arsip', 'desc')->paginate(10);

        // Get archived surat keluar
        $suratKeluarQuery = SuratKeluar::with(['klasifikasi', 'unitPembuat'])
            ->whereIn('status_arsip', ['aktif', 'inaktif', 'dimusnahkan']);
        
        if (isset($filter['status_arsip'])) {
            $suratKeluarQuery->where('status_arsip', $filter['status_arsip']);
        }
        
        if (isset($filter['klasifikasi_id'])) {
            $suratKeluarQuery->where('klasifikasi_id', $filter['klasifikasi_id']);
        }
        
        $suratKeluar = $suratKeluarQuery->orderBy('tanggal_arsip', 'desc')->paginate(10);

        // Statistics
        $stats = [
            'total_aktif' => SuratMasuk::where('status_arsip', 'aktif')->count() + SuratKeluar::where('status_arsip', 'aktif')->count(),
            'total_inaktif' => SuratMasuk::where('status_arsip', 'inaktif')->count() + SuratKeluar::where('status_arsip', 'inaktif')->count(),
            'total_dimusnahkan' => SuratMasuk::where('status_arsip', 'dimusnahkan')->count() + SuratKeluar::where('status_arsip', 'dimusnahkan')->count(),
            'jatuh_tempo_bulan_ini' => count($this->service->getJatuhTempoArsip(1)),
        ];

        return view('arsip.index', compact('suratMasuk', 'suratKeluar', 'stats', 'filter'));
    }

    /**
     * Archive surat masuk
     */
    public function archiveSuratMasuk(SuratMasuk $suratMasuk)
    {
        $result = $this->service->archiveSuratMasuk($suratMasuk);

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * Archive surat keluar
     */
    public function archiveSuratKeluar(SuratKeluar $suratKeluar)
    {
        $result = $this->service->archiveSuratKeluar($suratKeluar);

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * Show jatuh tempo report
     */
    public function jatuhTempo(Request $request)
    {
        $monthsAhead = $request->get('months_ahead', 3);
        $type = $request->get('type', 'all');
        
        $jatuhTempoList = $this->service->getJatuhTempoArsip((int) $monthsAhead, $type);

        return view('arsip.jatuh-tempo', compact('jatuhTempoList', 'monthsAhead', 'type'));
    }

    /**
     * Export jatuh tempo report to Excel
     */
    public function exportJatuhTempo(Request $request)
    {
        $monthsAhead = $request->get('months_ahead', 3);
        $type = $request->get('type', 'all');
        
        $jatuhTempoList = $this->service->getJatuhTempoArsip((int) $monthsAhead, $type);

        // Simple CSV export (can be enhanced with Laravel Excel package)
        $filename = 'laporan_jatuh_tempo_' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($jatuhTempoList) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, [
                'Tipe',
                'Nomor Surat',
                'Perihal',
                'Klasifikasi',
                'Jatuh Tempo Type',
                'Tanggal Jatuh Tempo',
                'Sisa Bulan',
            ]);

            // Data
            foreach ($jatuhTempoList as $item) {
                $model = $item['model'];
                fputcsv($file, [
                    $item['type'] === 'surat_masuk' ? 'Surat Masuk' : 'Surat Keluar',
                    $model->nomor_surat ?? $model->nomor_surat_final,
                    $model->perihal,
                    $model->klasifikasi?->nama ?? '-',
                    ucfirst($item['jatuh_tempo_type']),
                    $item['tanggal_jatuh_tempo']->format('d/m/Y'),
                    $item['sisa_bulan'],
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Show form for creating berita acara pemusnahan
     */
    public function createBeritaAcara(Request $request)
    {
        // Get arsip that are ready for destruction (past jatuh tempo inaktif)
        $readyForDestruction = [];
        
        $suratMasukReady = SuratMasuk::where('status_arsip', 'inaktif')
            ->whereNotNull('tanggal_jatuh_inaktif')
            ->where('tanggal_jatuh_inaktif', '<=', now())
            ->with(['klasifikasi'])
            ->get();

        foreach ($suratMasukReady as $surat) {
            $readyForDestruction[] = [
                'type' => 'surat_masuk',
                'model' => $surat,
            ];
        }

        $suratKeluarReady = SuratKeluar::where('status_arsip', 'inaktif')
            ->whereNotNull('tanggal_jatuh_inaktif')
            ->where('tanggal_jatuh_inaktif', '<=', now())
            ->with(['klasifikasi'])
            ->get();

        foreach ($suratKeluarReady as $surat) {
            $readyForDestruction[] = [
                'type' => 'surat_keluar',
                'model' => $surat,
            ];
        }

        return view('arsip.pemusnahan.create', compact('readyForDestruction'));
    }

    /**
     * Store berita acara pemusnahan
     */
    public function storeBeritaAcara(Request $request)
    {
        $request->validate([
            'tanggal_berita_acara' => 'required|date',
            'keterangan' => 'nullable|string',
            'arsip_list' => 'required|array',
            'arsip_list.*.type' => 'required|in:surat_masuk,surat_keluar',
            'arsip_list.*.id' => 'required|integer',
        ]);

        $result = $this->service->createBeritaAcara(
            $request->only(['tanggal_berita_acara', 'keterangan']),
            $request->input('arsip_list', [])
        );

        if ($result['success']) {
            return redirect()->route('arsip.berita-acara.show', $result['data']->id)
                ->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message'])->withInput();
    }

    /**
     * Show berita acara details
     */
    public function showBeritaAcara(BeritaAcaraPemusnahan $beritaAcara)
    {
        $beritaAcara->load(['details', 'creator', 'approver']);
        
        return view('arsip.pemusnahan.show', compact('beritaAcara'));
    }

    /**
     * List all berita acara
     */
    public function listBeritaAcara()
    {
        $beritaAcaraList = BeritaAcaraPemusnahan::with(['creator', 'details'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('arsip.pemusnahan.index', compact('beritaAcaraList'));
    }

    /**
     * Soft delete surat
     */
    public function destroy($type, $id, Request $request)
    {
        $request->validate([
            'alasan_hapus' => 'required|string|max:500',
        ]);

        if ($type === 'surat_masuk') {
            $surat = SuratMasuk::findOrFail($id);
        } else {
            $surat = SuratKeluar::findOrFail($id);
        }

        $result = $this->service->softDeleteSurat($surat, $request->input('alasan_hapus'));

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * Restore soft deleted surat
     */
    public function restore($type, $id)
    {
        if ($type === 'surat_masuk') {
            $surat = SuratMasuk::withTrashed()->findOrFail($id);
        } else {
            $surat = SuratKeluar::withTrashed()->findOrFail($id);
        }

        $result = $this->service->restoreSurat($surat);

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * Show trash (soft deleted items)
     */
    public function trash(Request $request)
    {
        $type = $request->get('type', 'all');
        
        $trashItems = [];

        if ($type === 'all' || $type === 'surat_masuk') {
            $suratMasukTrash = SuratMasuk::onlyTrashed()
                ->whereNotNull('deleted_until')
                ->orderBy('deleted_until', 'desc')
                ->get();

            foreach ($suratMasukTrash as $surat) {
                $trashItems[] = [
                    'type' => 'surat_masuk',
                    'model' => $surat,
                    'can_restore' => $surat->canBeRestored(),
                ];
            }
        }

        if ($type === 'all' || $type === 'surat_keluar') {
            $suratKeluarTrash = SuratKeluar::onlyTrashed()
                ->whereNotNull('deleted_until')
                ->orderBy('deleted_until', 'desc')
                ->get();

            foreach ($suratKeluarTrash as $surat) {
                $trashItems[] = [
                    'type' => 'surat_keluar',
                    'model' => $surat,
                    'can_restore' => $surat->canBeRestored(),
                ];
            }
        }

        return view('arsip.trash', compact('trashItems', 'type'));
    }

    /**
     * Show notifications
     */
    public function notifications()
    {
        $notifications = ArsipNotification::with('arsip')
            ->orderBy('sent_at', 'desc')
            ->paginate(20);

        return view('arsip.notifications', compact('notifications'));
    }

    /**
     * Mark notification as read
     */
    public function markNotificationAsRead(ArsipNotification $notification)
    {
        $notification->markAsRead();
        
        return redirect()->back()->with('success', 'Notifikasi ditandai sebagai sudah dibaca');
    }
}
