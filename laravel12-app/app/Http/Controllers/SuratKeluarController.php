<?php

namespace App\Http\Controllers;

use App\Models\SuratKeluar;
use App\Models\Unit;
use App\Models\KlasifikasiArsip;
use App\Models\SifatSurat;
use App\Enums\SuratKeluarStatus;
use App\Services\SuratKeluarWorkflowService;
use App\Http\Requests\StoreSuratKeluarRequest;
use App\Http\Requests\UpdateSuratKeluarRequest;
use App\Http\Requests\TransitionSuratKeluarRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class SuratKeluarController extends Controller
{
    public function __construct(
        private SuratKeluarWorkflowService $workflowService
    ) {}

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = SuratKeluar::with(['unitPembuat', 'klasifikasi', 'sifat', 'creator'])
                ->select('surat_keluar.*');

            // Filter by unit (Kabag hanya lihat unitnya sendiri)
            if (!Auth::user()->can('surat_keluar.view.all')) {
                $query->where('unit_pembuat_id', Auth::user()->unit_id);
            }

            // Filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('klasifikasi_id')) {
                $query->where('klasifikasi_id', $request->klasifikasi_id);
            }
            if ($request->filled('sifat_id')) {
                $query->where('sifat_id', $request->sifat_id);
            }
            if ($request->filled('from_date') && $request->filled('to_date')) {
                $query->whereBetween('created_at', [$request->from_date, $request->to_date]);
            }

            return datatables()->of($query)
                ->addColumn('status_badge', function($row) {
                    return '<span class="badge bg-' . $row->status->color() . '">' . $row->status->label() . '</span>';
                })
                ->addColumn('action', function($row) {
                    $btn = '<a href="' . route('surat-keluar.show', $row->id) . '" class="btn btn-sm btn-info text-white"><i class="bi bi-eye"></i></a> ';
                    if ($row->canEdit()) {
                        $btn .= '<a href="' . route('surat-keluar.edit', $row->id) . '" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a> ';
                        $btn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '"><i class="bi bi-trash"></i></button>';
                    }
                    return $btn;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        $klasifikasis = KlasifikasiArsip::root()->get();
        $sifats = SifatSurat::all();
        return view('surat-keluar.index', compact('klasifikasis', 'sifats'));
    }

    public function create()
    {
        Gate::authorize('surat_keluar.create');
        
        $units = Unit::all();
        $klasifikasis = KlasifikasiArsip::all();
        $sifats = SifatSurat::all();
        
        return view('surat-keluar.create', compact('units', 'klasifikasis', 'sifats'));
    }

    public function store(StoreSuratKeluarRequest $request)
    {
        Gate::authorize('surat_keluar.create');

        $data = $request->validated();
        $data['created_by'] = Auth::id();
        $data['status'] = SuratKeluarStatus::Draft;

        $surat = SuratKeluar::create($data);

        return redirect()->route('surat-keluar.show', $surat)
            ->with('success', 'Surat keluar berhasil dibuat sebagai draft.');
    }

    public function show(SuratKeluar $suratKeluar)
    {
        Gate::authorize('view-surat-unit', $suratKeluar);

        $suratKeluar->load(['histories.user', 'klasifikasi', 'sifat', 'creator', 'unitPembuat']);
        
        $availableTransitions = $this->workflowService->getAvailableTransitions($suratKeluar);

        return view('surat-keluar.show', compact('suratKeluar', 'availableTransitions'));
    }

    public function edit(SuratKeluar $suratKeluar)
    {
        Gate::authorize('surat_keluar.create');
        
        if (!$suratKeluar->canEdit()) {
            abort(403, 'Surat tidak dapat diedit karena status bukan Draft.');
        }

        $units = Unit::all();
        $klasifikasis = KlasifikasiArsip::all();
        $sifats = SifatSurat::all();

        return view('surat-keluar.edit', compact('suratKeluar', 'units', 'klasifikasis', 'sifats'));
    }

    public function update(UpdateSuratKeluarRequest $request, SuratKeluar $suratKeluar)
    {
        Gate::authorize('surat_keluar.create');

        if (!$suratKeluar->canEdit()) {
            abort(403, 'Surat tidak dapat diupdate karena status bukan Draft.');
        }

        $suratKeluar->update($request->validated());

        return redirect()->route('surat-keluar.show', $suratKeluar)
            ->with('success', 'Surat keluar berhasil diupdate.');
    }

    public function destroy(SuratKeluar $suratKeluar)
    {
        Gate::authorize('surat_keluar.create');

        if (!$suratKeluar->canDelete()) {
            return response()->json([
                'success' => false,
                'message' => 'Surat tidak dapat dihapus karena status bukan Draft atau Ditolak.'
            ], 403);
        }

        $suratKeluar->delete();

        return response()->json(['success' => true, 'message' => 'Surat keluar berhasil dihapus.']);
    }

    public function transition(TransitionSuratKeluarRequest $request, SuratKeluar $suratKeluar)
    {
        $newStatus = $request->getNewStatus();
        $notes = $request->input('notes');

        try {
            $this->workflowService->transition($suratKeluar, $newStatus, $notes);

            return redirect()->route('surat-keluar.show', $suratKeluar)
                ->with('success', "Status berhasil diubah menjadi {$newStatus->label()}.");
        } catch (\Exception $e) {
            return redirect()->route('surat-keluar.show', $suratKeluar)
                ->with('error', $e->getMessage());
        }
    }

    public function submitReview(SuratKeluar $suratKeluar)
    {
        Gate::authorize('surat_keluar.create');
        
        if (!$suratKeluar->canEdit()) {
            abort(403, 'Hanya surat dengan status Draft yang bisa disubmit ke review.');
        }

        try {
            $this->workflowService->transition($suratKeluar, SuratKeluarStatus::Review);
            
            return redirect()->route('surat-keluar.show', $suratKeluar)
                ->with('success', 'Surat berhasil dikirim ke review.');
        } catch (\Exception $e) {
            return redirect()->route('surat-keluar.show', $suratKeluar)
                ->with('error', $e->getMessage());
        }
    }

    public function approve(SuratKeluar $suratKeluar, Request $request)
    {
        Gate::authorize('surat_keluar.approve');

        try {
            $this->workflowService->transition(
                $suratKeluar, 
                SuratKeluarStatus::Disetujui, 
                $request->input('catatan')
            );

            return redirect()->route('surat-keluar.show', $suratKeluar)
                ->with('success', 'Surat berhasil disetujui.');
        } catch (\Exception $e) {
            return redirect()->route('surat-keluar.show', $suratKeluar)
                ->with('error', $e->getMessage());
        }
    }

    public function reject(SuratKeluar $suratKeluar, Request $request)
    {
        $request->validate([
            'alasan' => 'required|string|max:1000'
        ]);

        try {
            $this->workflowService->transition($suratKeluar, SuratKeluarStatus::Ditolak, $request->input('alasan'));

            return redirect()->route('surat-keluar.show', $suratKeluar)
                ->with('success', 'Surat ditolak dan dikembalikan ke pembuat.');
        } catch (\Exception $e) {
            return redirect()->route('surat-keluar.show', $suratKeluar)
                ->with('error', $e->getMessage());
        }
    }

    public function prepareSign(SuratKeluar $suratKeluar, Request $request)
    {
        Gate::authorize('surat_keluar.create');

        $request->validate([
            'nomor_surat_final' => 'required|string|max:100',
            'tanggal_surat_final' => 'required|date',
        ]);

        $suratKeluar->update([
            'nomor_surat_final' => $request->nomor_surat_final,
            'tanggal_surat_final' => $request->tanggal_surat_final,
        ]);

        try {
            $this->workflowService->transition($suratKeluar, SuratKeluarStatus::SiapTandatangan);

            return redirect()->route('surat-keluar.show', $suratKeluar)
                ->with('success', 'Surat siap untuk ditandatangani.');
        } catch (\Exception $e) {
            return redirect()->route('surat-keluar.show', $suratKeluar)
                ->with('error', $e->getMessage());
        }
    }

    public function sign(SuratKeluar $suratKeluar)
    {
        Gate::authorize('surat_keluar.ttd');

        try {
            $this->workflowService->transition($suratKeluar, SuratKeluarStatus::Tertandatangani);

            return redirect()->route('surat-keluar.show', $suratKeluar)
                ->with('success', 'Surat berhasil ditandatangani.');
        } catch (\Exception $e) {
            return redirect()->route('surat-keluar.show', $suratKeluar)
                ->with('error', $e->getMessage());
        }
    }

    public function send(SuratKeluar $suratKeluar, Request $request)
    {
        Gate::authorize('surat_keluar.create');

        $request->validate([
            'cara_kirim' => 'required|in:langsung,pos,kurir,email',
            'tanggal_kirim' => 'required|date',
            'resi' => 'nullable|string|max:100',
        ]);

        $suratKeluar->update([
            'cara_kirim' => $request->cara_kirim,
            'tanggal_kirim' => $request->tanggal_kirim,
            'resi' => $request->resi,
        ]);

        try {
            $this->workflowService->transition($suratKeluar, SuratKeluarStatus::Terkirim);

            return redirect()->route('surat-keluar.show', $suratKeluar)
                ->with('success', 'Surat berhasil dikirim.');
        } catch (\Exception $e) {
            return redirect()->route('surat-keluar.show', $suratKeluar)
                ->with('error', $e->getMessage());
        }
    }
}
