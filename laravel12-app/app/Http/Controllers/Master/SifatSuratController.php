<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\SifatSurat;
use Illuminate\Http\Request;

class SifatSuratController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = SifatSurat::withTrashed()->select('sifat_surats.*');
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
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('master.sifat.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:50|unique:sifat_surats,nama',
            'keterangan' => 'nullable|string|max:255'
        ]);
        SifatSurat::create($validated);
        return response()->json(['success' => true]);
    }

    public function update(Request $request, SifatSurat $sifatSurat)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:50|unique:sifat_surats,nama,'.$sifatSurat->id,
            'keterangan' => 'nullable|string|max:255'
        ]);
        $sifatSurat->update($validated);
        return response()->json(['success' => true]);
    }

    public function destroy(SifatSurat $sifatSurat)
    {
        $sifatSurat->delete();
        return response()->json(['success' => true]);
    }

    public function restore($id)
    {
        SifatSurat::withTrashed()->findOrFail($id)->restore();
        return response()->json(['success' => true]);
    }
}
