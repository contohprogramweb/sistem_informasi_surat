<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\ImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SystemController extends Controller
{
    /**
     * Tampilkan halaman Audit Trail (Admin TU only)
     */
    public function auditTrail(Request $request)
    {
        // Middleware check - hanya Admin TU
        if (!Auth::check() || !in_array(Auth::user()->role, ['admin', 'staff_tu'])) {
            abort(403, 'Akses ditolak. Hanya Admin TU yang dapat melihat audit trail.');
        }

        $query = AuditLog::with('user');

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by entity
        if ($request->filled('entity')) {
            $query->where('entity', $request->entity);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
        } elseif ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        } elseif ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Sort
        $sortField = $request->get('sort', 'created_at');
        $sortDir = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDir);

        // Pagination
        $logs = $query->paginate(50)->withQueryString();

        // Get unique entities for filter dropdown
        $entities = AuditLog::selectDistinct('entity')
            ->pluck('entity')
            ->map(fn($e) => class_basename($e));

        // Get users for filter dropdown
        $users = \App\Models\User::select('id', 'name', 'role')->get();

        return view('admin.audit-trail.index', compact('logs', 'entities', 'users'));
    }

    /**
     * Export Audit Trail ke Excel/CSV
     */
    public function exportAuditTrail(Request $request)
    {
        if (!Auth::check() || !in_array(Auth::user()->role, ['admin', 'staff_tu'])) {
            abort(403);
        }

        $format = $request->get('format', 'excel');

        // Rebuild query sama seperti auditTrail()
        $query = AuditLog::with('user');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('entity')) {
            $query->where('entity', $request->entity);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
        }

        $logs = $query->orderBy('created_at', 'desc')->get();

        if ($format === 'csv') {
            return $this->exportCsv($logs);
        }

        return $this->exportExcel($logs);
    }

    private function exportExcel($logs)
    {
        $filename = 'audit-trail-' . now()->format('Y-m-d-His') . '.xlsx';
        
        // Simple Excel export using response
        $data = $logs->map(fn($log) => [
            'Tanggal' => $log->created_at->format('d/m/Y H:i:s'),
            'User' => $log->user?->name ?? 'System',
            'Aksi' => ucfirst($log->action),
            'Entity' => class_basename($log->entity),
            'ID Entity' => $log->entity_id,
            'IP Address' => $log->ip_address,
            'Perubahan' => json_encode($log->diff, JSON_UNESCAPED_UNICODE),
        ]);

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\AuditTrailExport($data),
            $filename
        );
    }

    private function exportCsv($logs)
    {
        $filename = 'audit-trail-' . now()->format('Y-m-d-His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, ['Tanggal', 'User', 'Aksi', 'Entity', 'ID Entity', 'IP Address', 'Perubahan']);
            
            // Data rows
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->created_at->format('d/m/Y H:i:s'),
                    $log->user?->name ?? 'System',
                    ucfirst($log->action),
                    class_basename($log->entity),
                    $log->entity_id,
                    $log->ip_address,
                    json_encode($log->diff, JSON_UNESCAPED_UNICODE),
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Tampilkan Error Log (Admin only)
     */
    public function errorLogs(Request $request)
    {
        if (!Auth::check() || !in_array(Auth::user()->role, ['admin', 'staff_tu'])) {
            abort(403);
        }

        $logFile = storage_path('logs/error.log');
        $lines = $request->get('lines', 50);

        $errors = [];
        
        if (File::exists($logFile)) {
            $file = File::get($logFile);
            $allLines = explode("\n", $file);
            
            // Ambil N baris terakhir
            $recentLines = array_slice($allLines, -$lines);
            
            // Parse simple log format
            $currentError = null;
            foreach ($recentLines as $line) {
                if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]\s+(\w+)\./', $line, $matches)) {
                    if ($currentError) {
                        $errors[] = $currentError;
                    }
                    $currentError = [
                        'timestamp' => $matches[1],
                        'level' => $matches[2],
                        'message' => trim(preg_replace('/^\[[^\]]+\]\s+\w+\.[^:]+:\s+/', '', $line)),
                        'trace' => [],
                    ];
                } elseif ($currentError) {
                    $currentError['trace'][] = $line;
                }
            }
            
            if ($currentError) {
                $errors[] = $currentError;
            }
        }

        return view('admin.error-logs.index', compact('errors', 'lines', 'logFile'));
    }

    /**
     * Halaman Import Data
     */
    public function importIndex()
    {
        return view('admin.import.index');
    }

    /**
     * Preview file sebelum import
     */
    public function preview(Request $request, ImportService $importService)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:10240',
            'type' => 'required|in:surat_masuk,surat_keluar,users,klasifikasi',
        ]);

        $result = $importService->preview($request->file('file'), $request->type);

        return response()->json($result);
    }

    /**
     * Proses import data
     */
    public function processImport(Request $request, ImportService $importService)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:10240',
            'type' => 'required|in:surat_masuk,surat_keluar,users,klasifikasi',
        ]);

        try {
            $batch = $importService->import(
                $request->file('file'),
                $request->type,
                $request->get('column_mapping', [])
            );

            return redirect()
                ->route('admin.import.history', $batch->id)
                ->with('success', "Import selesai. Berhasil: {$batch->success_count}, Gagal: {$batch->failed_count}");
        } catch (\Exception $e) {
            return back()
                ->withErrors(['file' => 'Gagal import: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Riwayat import
     */
    public function importHistory($id = null)
    {
        $query = \App\Models\ImportBatch::with('user')->latest();
        
        if ($id) {
            $batch = $query->findOrFail($id);
            return view('admin.import.history-detail', compact('batch'));
        }

        $batches = $query->paginate(20);
        return view('admin.import.history', compact('batches'));
    }
}
