<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\SuratMasuk;
use App\Models\User;
use App\Services\DisposisiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DisposisiController extends Controller
{
    protected DisposisiService $disposisiService;

    public function __construct(DisposisiService $disposisiService)
    {
        $this->disposisiService = $disposisiService;
    }

    /**
     * Dashboard widget: Disposisi Masuk untuk user yang login
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Disposisi::where('ke_user_id', Auth::id())
                ->with(['suratMasuk', 'dariUser', 'parent']);

            // Filter status jika ada
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            return datatables()->of($query)
                ->addColumn('surat_info', function($row) {
                    return $row->suratMasuk ? 
                        '<strong>' . e($row->suratMasuk->nomor_surat) . '</strong><br>' .
                        '<small>' . e(substr($row->suratMasuk->perihal, 0, 50)) . '...</small>' : '-';
                })
                ->addColumn('dari', function($row) {
                    return $row->dariUser->name ?? 'Unknown';
                })
                ->addColumn('prioritas_badge', function($row) {
                    $colors = [
                        'Rendah' => 'secondary',
                        'Normal' => 'info',
                        'Tinggi' => 'warning',
                        'Segera' => 'danger',
                    ];
                    $color = $colors[$row->prioritas] ?? 'secondary';
                    return '<span class="badge bg-' . $color . '">' . e($row->prioritas) . '</span>';
                })
                ->addColumn('status_badge', function($row) {
                    return '<span class="badge bg-' . $row->status_color . '">' . e($row->status) . '</span>';
                })
                ->addColumn('batas_waktu', function($row) {
                    if (!$row->batas_waktu) return '-';
                    $isOverdue = $row->isOverdue();
                    $class = $isOverdue ? 'text-danger fw-bold' : '';
                    return '<span class="' . $class . '">' . $row->batas_waktu->format('d M Y') . '</span>';
                })
                ->addColumn('action', function($row) {
                    $btn = '<a href="' . route('disposisi.show', $row->id) . '" class="btn btn-sm btn-primary"><i class="bi bi-eye"></i></a> ';
                    return $btn;
                })
                ->rawColumns(['surat_info', 'prioritas_badge', 'status_badge', 'batas_waktu', 'action'])
                ->make(true);
        }

        return view('disposisi.index');
    }

    /**
     * Detail disposisi dengan timeline
     */
    public function show(Disposisi $disposisi)
    {
        // Authorization: hanya penerima, pemberi, atau pimpinan yang bisa lihat
        if ($disposisi->ke_user_id !== Auth::id() && 
            $disposisi->dari_user_id !== Auth::id() && 
            !Auth::user()->can('surat_masuk.view.all')) {
            abort(403);
        }

        // Mark as read otomatis saat dibuka
        $this->disposisiService->markAsRead($disposisi);

        // Dapatkan timeline
        $timeline = $this->disposisiService->getTimeline($disposisi->surat_masuk_id);

        // Dapatkan disposisi lain pada surat yang sama
        $otherDisposisi = Disposisi::where('surat_masuk_id', $disposisi->surat_masuk_id)
            ->where('id', '!=', $disposisi->id)
            ->with(['dariUser', 'keUser'])
            ->get();

        return view('disposisi.show', compact('disposisi', 'timeline', 'otherDisposisi'));
    }

    /**
     * Store disposisi baru (dari detail surat masuk)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'surat_masuk_id' => 'required|exists:surat_masuk,id',
            'ke_user_id' => 'required|exists:users,id',
            'instruksi' => 'required|string',
            'batas_waktu' => 'nullable|date|after_or_equal:today',
            'prioritas' => 'required|in:Rendah,Normal,Tinggi,Segera',
            'tembusan' => 'nullable|array',
            'tembusan.*' => 'exists:users,id',
        ]);

        try {
            $disposisi = $this->disposisiService->createDisposisi($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Disposisi berhasil dibuat',
                'data' => $disposisi,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat disposisi: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Forward disposisi ke staf di unit yang sama
     */
    public function forward(Request $request, Disposisi $disposisi)
    {
        // Validasi: hanya Kabag atau yang menerima disposisi yang bisa forward
        if ($disposisi->ke_user_id !== Auth::id() && 
            !Auth::user()->can('disposisi.forward')) {
            abort(403);
        }

        $validated = $request->validate([
            'ke_user_id' => 'required|exists:users,id',
            'instruksi' => 'required|string',
            'batas_waktu' => 'nullable|date|after_or_equal:today',
            'prioritas' => 'nullable|in:Rendah,Normal,Tinggi,Segera',
            'tembusan' => 'nullable|array',
            'tembusan.*' => 'exists:users,id',
        ]);

        try {
            $newDisposisi = $this->disposisiService->forwardDisposisi($disposisi, $validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Disposisi berhasil diteruskan',
                'data' => $newDisposisi,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update status disposisi (Sedang Ditindaklanjuti / Selesai / Belum Selesai)
     */
    public function updateStatus(Request $request, Disposisi $disposisi)
    {
        // Authorization
        if ($disposisi->ke_user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:Sedang Ditindaklanjuti,Selesai,Belum Selesai',
            'komentar' => 'nullable|string|max:1000',
            'file_tindak_lanjut' => 'nullable|file|mimes:pdf,jpg,png|max:10240',
        ]);

        $filePath = null;
        if ($request->hasFile('file_tindak_lanjut')) {
            $filePath = $request->file('file_tindak_lanjut')->store('disposisi-tindak-lanjut', 'public');
        }

        $this->disposisiService->updateStatus(
            $disposisi, 
            $validated['status'], 
            $validated['komentar'] ?? null, 
            $filePath
        );

        return response()->json([
            'success' => true,
            'message' => 'Status disposisi berhasil diupdate',
        ]);
    }

    /**
     * Disposisi Massal (max 50 surat)
     */
    public function massal(Request $request)
    {
        $validated = $request->validate([
            'surat_ids' => 'required|array|max:50',
            'surat_ids.*' => 'exists:surat_masuk,id',
            'ke_user_id' => 'required|exists:users,id',
            'instruksi' => 'required|string',
            'batas_waktu' => 'nullable|date|after_or_equal:today',
            'prioritas' => 'required|in:Rendah,Normal,Tinggi,Segera',
        ]);

        DB::beginTransaction();
        try {
            $createdCount = 0;
            foreach ($validated['surat_ids'] as $suratId) {
                $data = [
                    'surat_masuk_id' => $suratId,
                    'ke_user_id' => $validated['ke_user_id'],
                    'instruksi' => $validated['instruksi'],
                    'batas_waktu' => $validated['batas_waktu'] ?? null,
                    'prioritas' => $validated['prioritas'],
                    'tembusan' => $request->tembusan ?? [],
                ];
                
                $this->disposisiService->createDisposisi($data);
                $createdCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil membuat {$createdCount} disposisi",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat disposisi massal: ' . $e->getMessage(),
            ], 422);
        }
    }
}
