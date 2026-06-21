<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\TemplateDisposisi;
use App\Models\User;
use App\Http\Requests\StoreTemplateDisposisiRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TemplateDisposisiController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = TemplateDisposisi::where('user_id', Auth::id())->withTrashed();

            return datatables()->of($data)
                ->addColumn('action', function($row){
                    $btn = '<button type="button" class="btn btn-sm btn-info text-white btn-edit" data-id="'.$row->id.'"><i class="bi bi-pencil"></i></button> ';
                    $btn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="'.$row->id.'"><i class="bi bi-trash"></i></button>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('master.template-disposisi.index');
    }

    public function store(StoreTemplateDisposisiRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();

        TemplateDisposisi::create($data);
        return response()->json(['success' => true, 'message' => 'Template berhasil disimpan']);
    }

    public function show(TemplateDisposisi $template)
    {
        if ($template->user_id !== Auth::id()) {
            abort(403);
        }
        return response()->json($template);
    }

    public function update(StoreTemplateDisposisiRequest $request, TemplateDisposisi $template)
    {
        if ($template->user_id !== Auth::id()) {
            abort(403);
        }
        $template->update($request->validated());
        return response()->json(['success' => true]);
    }

    public function destroy(TemplateDisposisi $template)
    {
        if ($template->user_id !== Auth::id()) {
            abort(403);
        }
        $template->delete();
        return response()->json(['success' => true]);
    }
}
