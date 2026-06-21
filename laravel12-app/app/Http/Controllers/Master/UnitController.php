<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Unit::withTrashed()->select('units.*');

            return datatables()->of($data)
                ->addColumn('action', function($row){
                    $btn = '<button type="button" class="btn btn-sm btn-info text-white btn-edit" data-id="'.$row->id.'"><i class="bi bi-pencil"></i></button> ';
                    if($row->deleted_at) {
                        $btn .= '<button type="button" class="btn btn-sm btn-success btn-restore" data-id="'.$row->id.'"><i class="bi bi-arrow-counterclockwise"></i></button>';
                    } else {
                        $btn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="'.$row->id.'"><i class="bi bi-trash"></i></button>';
                    }
                    return $btn;
                })
                ->addColumn('status', function($row){
                    return $row->deleted_at ? '<span class="badge bg-danger">Terhapus</span>' : '<span class="badge bg-success">Aktif</span>';
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }
        return view('master.units.index');
    }

    public function store(StoreUnitRequest $request)
    {
        Unit::create($request->validated());
        return response()->json(['success' => true, 'message' => 'Unit berhasil ditambahkan']);
    }

    public function show(Unit $unit)
    {
        return response()->json($unit);
    }

    public function update(UpdateUnitRequest $request, Unit $unit)
    {
        $unit->update($request->validated());
        return response()->json(['success' => true, 'message' => 'Unit berhasil diupdate']);
    }

    public function destroy(Unit $unit)
    {
        $hasUser = $unit->users()->count() > 0;
        $hasSurat = $unit->suratMasuk()->count() > 0;

        if ($hasUser || $hasSurat) {
            return response()->json([
                'success' => false,
                'message' => 'Unit tidak dapat dihapus karena masih memiliki data terkait (User atau Surat).'
            ], 422);
        }

        $unit->delete();
        return response()->json(['success' => true, 'message' => 'Unit berhasil dihapus (soft delete)']);
    }

    public function restore($id)
    {
        $unit = Unit::withTrashed()->findOrFail($id);
        $unit->restore();
        return response()->json(['success' => true, 'message' => 'Unit berhasil dipulihkan']);
    }
}
