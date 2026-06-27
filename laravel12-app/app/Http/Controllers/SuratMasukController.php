<?php

namespace App\Http\Controllers;

use App\Models\SuratMasuk;
use App\Models\Unit;
use App\Models\KlasifikasiArsip;
use App\Models\SifatSurat;
use App\Http\Requests\StoreSuratMasukRequest;
use App\Http\Requests\UpdateSuratMasukRequest;
use App\Services\AgendaNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class SuratMasukController extends Controller
{
    protected AgendaNumberService $agendaService;

    public function __construct(AgendaNumberService $agendaService)
    {
        $this->agendaService = $agendaService;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = SuratMasuk::with(['klasifikasi', 'sifat', 'unitTujuan'])
                ->select('surat_masuk.*');

            // Apply filters
            $filters = $request->only([
                'tanggal_mulai', 'tanggal_sampai', 
                'klasifikasi_id', 'unit_id', 'sifat_id', 
                'prioritas', 'status'
            ]);
            $query->filter($filters);

            return DataTables::of($query)
                ->addColumn('agenda', function($row) {
                    return $row->agenda ?? '-';
                })
                ->addColumn('tanggal_terima', function($row) {
                    return $row->tanggal_terima->format('d/m/Y');
                })
                ->addColumn('pengirim', function($row) {
                    return $row->pengirim;
                })
                ->addColumn('perihal', function($row) {
                    return Str::limit($row->perihal, 50);
                })
                ->addColumn('status', function($row) {
                    return '<span class="badge '.$row->status_badge_class.'">'.$row->status.'</span>';
                })
                ->addColumn('prioritas', function($row) {
                    return '<span class="badge '.$row->prioritas_badge_class.'">'.$row->prioritas.'</span>';
                })
                ->addColumn('action', function($row) {
                    $btn = '<a href="'.route('surat-masuk.show', $row->id).'" class="btn btn-sm btn-info text-white"><i class="bi bi-eye"></i></a> ';
                    $btn .= '<button type="button" class="btn btn-sm btn-warning btn-edit" data-id="'.$row->id.'"><i class="bi bi-pencil"></i></button> ';
                    $btn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="'.$row->id.'"><i class="bi bi-trash"></i></button>';
                    return $btn;
                })
                ->rawColumns(['status', 'prioritas', 'action'])
                ->make(true);
        }

        $units = Unit::orderBy('nama_unit')->get();
        $klasifikasis = KlasifikasiArsip::root()->with('children')->get();
        $sifatSurats = SifatSurat::orderBy('nama')->get();

        return view('surat-masuk.index', compact('units', 'klasifikasis', 'sifatSurats'));
    }

    public function create()
    {
        $units = Unit::orderBy('nama_unit')->get();
        $klasifikasis = KlasifikasiArsip::with('parent')->get();
        $sifatSurats = SifatSurat::orderBy('nama')->get();
        
        return view('surat-masuk.create', compact('units', 'klasifikasis', 'sifatSurats'));
    }

    public function store(StoreSuratMasukRequest $request)
    {
        try {
            DB::beginTransaction();

            // Get unit code for agenda generation (use first unit tujuan as primary)
            $primaryUnit = Unit::find($request->unit_tujuan[0]);
            $unitCode = $primaryUnit->kode_unit;
            
            // Generate agenda number
            $agendaNumber = $this->agendaService->generateNext($unitCode);

            // Create surat masuk
            $suratMasuk = SuratMasuk::create([
                'agenda' => $agendaNumber,
                'tanggal_terima' => $request->tanggal_terima,
                'cara_terima' => $request->cara_terima,
                'penerima_fisik' => $request->penerima_fisik,
                'nomor_surat' => $request->nomor_surat,
                'tanggal_surat' => $request->tanggal_surat,
                'pengirim' => $request->pengirim,
                'perihal' => $request->perihal,
                'ringkasan' => $request->ringkasan,
                'klasifikasi_id' => $request->klasifikasi_id,
                'sifat_id' => $request->sifat_id,
                'prioritas' => $request->prioritas,
                'indeks' => $request->indeks ?? [],
                'tidak_perlu_disposisi' => $request->tidak_perlu_disposisi ?? false,
                'status' => 'Aktif',
            ]);

            // Attach unit tujuan
            $suratMasuk->unitTujuan()->sync($request->unit_tujuan);

            // Handle file uploads
            if ($request->hasFile('lampiran')) {
                foreach ($request->file('lampiran') as $file) {
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('surat-masuk', $filename, 'public');
                    
                    $suratMasuk->lampiran()->create([
                        'filename' => $filename,
                        'original_name' => $file->getClientOriginalName(),
                        'hash' => hash_file('sha256', $file->getPathname()),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Surat masuk berhasil ditambahkan dengan nomor agenda: ' . $agendaNumber,
                'redirect' => route('surat-masuk.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(SuratMasuk $suratMasuk)
    {
        $suratMasuk->load(['klasifikasi', 'sifat', 'unitTujuan', 'lampiran', 'disposisi']);
        
        return view('surat-masuk.show', compact('suratMasuk'));
    }

    public function edit(SuratMasuk $suratMasuk)
    {
        $suratMasuk->load(['unitTujuan']);
        $units = Unit::orderBy('nama_unit')->get();
        $klasifikasis = KlasifikasiArsip::with('parent')->get();
        $sifatSurats = SifatSurat::orderBy('nama')->get();
        
        return view('surat-masuk.edit', compact('suratMasuk', 'units', 'klasifikasis', 'sifatSurats'));
    }

    public function update(UpdateSuratMasukRequest $request, SuratMasuk $suratMasuk)
    {
        try {
            DB::beginTransaction();

            // Update metadata (except agenda which is immutable)
            $suratMasuk->update([
                'tanggal_terima' => $request->tanggal_terima,
                'cara_terima' => $request->cara_terima,
                'penerima_fisik' => $request->penerima_fisik,
                'nomor_surat' => $request->nomor_surat,
                'tanggal_surat' => $request->tanggal_surat,
                'pengirim' => $request->pengirim,
                'perihal' => $request->perihal,
                'ringkasan' => $request->ringkasan,
                'klasifikasi_id' => $request->klasifikasi_id,
                'sifat_id' => $request->sifat_id,
                'prioritas' => $request->prioritas,
                'indeks' => $request->indeks ?? [],
                'tidak_perlu_disposisi' => $request->tidak_perlu_disposisi ?? false,
            ]);

            // Update unit tujuan
            $suratMasuk->unitTujuan()->sync($request->unit_tujuan);

            // Handle new file uploads
            if ($request->hasFile('lampiran')) {
                foreach ($request->file('lampiran') as $file) {
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('surat-masuk', $filename, 'public');
                    
                    $suratMasuk->lampiran()->create([
                        'filename' => $filename,
                        'original_name' => $file->getClientOriginalName(),
                        'hash' => hash_file('sha256', $file->getPathname()),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                    ]);
                }
            }

            // Log audit trail
            \App\Models\AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'update',
                'entity' => 'surat_masuk',
                'entity_id' => $suratMasuk->id,
                'old_values' => json_encode($request->except(['_token', '_method'])),
                'new_values' => json_encode($suratMasuk->getChanges()),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Surat masuk berhasil diupdate',
                'redirect' => route('surat-masuk.show', $suratMasuk->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(SuratMasuk $suratMasuk)
    {
        try {
            // Soft delete with retention 30 days
            $suratMasuk->deleted_until = now()->addDays(30);
            $suratMasuk->status = 'Dihapus';
            $suratMasuk->save();
            $suratMasuk->delete();

            return response()->json([
                'success' => true,
                'message' => 'Surat masuk berhasil dihapus (akan dipermanenkan setelah 30 hari)'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function archive(SuratMasuk $suratMasuk)
    {
        // Check if can be archived (no pending disposisi or sudah tidak perlu disposisi)
        if (!$suratMasuk->tidak_perlu_disposisi && $suratMasuk->disposisi()->where('status', '!=', 'Selesai')->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat mengarsipkan surat yang masih memiliki disposisi aktif'
            ], 422);
        }

        $suratMasuk->update(['status' => 'Diarsipkan']);

        return response()->json([
            'success' => true,
            'message' => 'Surat masuk berhasil diarsipkan'
        ]);
    }
}
