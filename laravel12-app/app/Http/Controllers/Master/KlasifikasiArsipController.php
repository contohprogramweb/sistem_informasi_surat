<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\KlasifikasiArsip;
use App\Http\Requests\StoreKlasifikasiRequest;
use App\Http\Requests\UpdateKlasifikasiRequest;
use Illuminate\Http\Request;

class KlasifikasiArsipController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = KlasifikasiArsip::with('parent')->withTrashed()->select('klasifikasi_arsip.*');

            return datatables()->of($data)
                ->addColumn('parent_name', function($row){
                    return $row->parent ? $row->parent->nama : '-';
                })
                ->addColumn('action', function($row){
                    $btn = '<button type="button" class="btn btn-sm btn-info text-white btn-edit" data-id="'.$row->id.'"><i class="bi bi-pencil"></i></button> ';
                    if($row->deleted_at) {
                         $btn .= '<button type="button" class="btn btn-sm btn-success btn-restore" data-id="'.$row->id.'"><i class="bi bi-arrow-counterclockwise"></i></button>';
                    } else {
                         $btn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="'.$row->id.'"><i class="bi bi-trash"></i></button>';
                    }
                    return $btn;
                })
                ->rawColumns(['parent_name', 'action'])
                ->make(true);
        }

        $treeData = $this->buildTree();
        return view('master.klasifikasi.index', compact('treeData'));
    }

    private function buildTree($parentId = null)
    {
        $branches = KlasifikasiArsip::where('parent_id', $parentId)->with('children')->get();
        foreach ($branches as $branch) {
            $branch->children = $this->buildTree($branch->id);
        }
        return $branches;
    }

    public function store(StoreKlasifikasiRequest $request)
    {
        KlasifikasiArsip::create($request->validated());
        return response()->json(['success' => true, 'message' => 'Klasifikasi berhasil ditambahkan']);
    }

    public function show(KlasifikasiArsip $klasifikasi)
    {
        return response()->json($klasifikasi);
    }

    public function update(UpdateKlasifikasiRequest $request, KlasifikasiArsip $klasifikasi)
    {
        $klasifikasi->update($request->validated());
        return response()->json(['success' => true, 'message' => 'Klasifikasi berhasil diupdate']);
    }

    public function destroy(KlasifikasiArsip $klasifikasi)
    {
        $hasChildren = $klasifikasi->children()->count() > 0;
        $hasSurat = $klasifikasi->suratMasuk()->count() > 0;

        if ($hasChildren || $hasSurat) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus klasifikasi yang masih memiliki anak atau surat terkait.'
            ], 422);
        }

        $klasifikasi->delete();
        return response()->json(['success' => true, 'message' => 'Klasifikasi berhasil dihapus']);
    }

    public function restore($id)
    {
        $item = KlasifikasiArsip::withTrashed()->findOrFail($id);
        $item->restore();
        return response()->json(['success' => true]);
    }

    public function treeList()
    {
        $data = KlasifikasiArsip::select('id', 'nama', 'parent_id')->get();
        return response()->json($data);
    }
}
