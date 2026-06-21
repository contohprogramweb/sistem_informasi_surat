<?php

namespace App\Http\Controllers;

use App\Services\SearchService;
use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use App\Models\Unit;
use App\Models\KlasifikasiArsip;
use App\Models\SifatSurat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SearchResultsExport;

class SearchController extends Controller
{
    protected $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * Display search page with filters
     */
    public function index(Request $request)
    {
        // Get filter options for dropdowns
        $units = Unit::orderBy('nama_unit')->get();
        $klasifikasis = KlasifikasiArsip::orderBy('kode')->get();
        $sifatSurats = SifatSurat::orderBy('nama_sifat')->get();
        
        $prioritasOptions = ['Rendah', 'Normal', 'Tinggi', 'Segera'];
        $statusMasukOptions = ['Aktif', 'Diarsipkan', 'Dalam Disposisi', 'Selesai'];
        $statusKeluarOptions = ['Draft', 'Review', 'Disetujui', 'Terkirim', 'Ditolak'];

        return view('search.index', compact(
            'units',
            'klasifikasis',
            'sifatSurats',
            'prioritasOptions',
            'statusMasukOptions',
            'statusKeluarOptions'
        ));
    }

    /**
     * Perform global search via AJAX
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:255',
            'type' => 'nullable|in:all,masuk,keluar',
            'sort_by' => 'nullable|in:relevance,tanggal,prioritas,agenda',
            'sort_order' => 'nullable|in:asc,desc',
            'status' => 'nullable|array',
            'klasifikasi_id' => 'nullable|integer',
            'unit_id' => 'nullable|array',
            'sifat_id' => 'nullable|array',
            'prioritas' => 'nullable|array',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_sampai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'surat_terkait' => 'nullable|integer',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $results = $this->searchService->search($validated);

        // Format results for DataTables
        $formattedResults = $this->formatForDataTables($results, $request->input('q', ''));

        return response()->json([
            'success' => true,
            'data' => $formattedResults,
            'total' => $results['total'],
            'query' => $results['query'],
        ]);
    }

    /**
     * Format search results for DataTables server-side processing
     */
    private function formatForDataTables(array $results, string $query): array
    {
        $formatted = [];

        // Format Surat Masuk results
        foreach ($results['surat_masuk'] as $item) {
            $formatted[] = [
                'id' => $item['id'],
                'jenis' => 'masuk',
                'agenda' => $item['agenda'] ?? '-',
                'nomor_surat' => $this->searchService->highlightTerms($item['nomor_surat'] ?? '', $query),
                'perihal' => $this->searchService->highlightTerms($item['perihal'] ?? '', $query),
                'pengirim_tujuan' => $this->searchService->highlightTerms($item['pengirim'] ?? '', $query),
                'tanggal' => $item['tanggal_terima'] ?? null,
                'prioritas' => $item['prioritas'] ?? 'Normal',
                'status' => $item['status'] ?? '-',
                'klasifikasi' => $item['klasifikasi']['nama_klasifikasi'] ?? '-',
                'unit' => $item['unitTujuan']->pluck('nama_unit')->join(', '),
                'relevance_score' => $item['relevance_score'] ?? 0,
            ];
        }

        // Format Surat Keluar results
        foreach ($results['surat_keluar'] as $item) {
            $formatted[] = [
                'id' => $item['id'],
                'jenis' => 'keluar',
                'agenda' => '-',
                'nomor_surat' => $this->searchService->highlightTerms($item['nomor_surat_final'] ?? '', $query),
                'perihal' => $this->searchService->highlightTerms($item['perihal'] ?? '', $query),
                'pengirim_tujuan' => $this->searchService->highlightTerms($item['tujuan'] ?? '', $query),
                'tanggal' => $item['tanggal_surat_final'] ?? null,
                'prioritas' => $item['sifat']['nama_sifat'] ?? 'Normal',
                'status' => $item['status']->value ?? '-',
                'klasifikasi' => $item['klasifikasi']['nama_klasifikasi'] ?? '-',
                'unit' => $item['unitPembuat']['nama_unit'] ?? '-',
                'relevance_score' => $item['relevance_score'] ?? 0,
            ];
        }

        return $formatted;
    }

    /**
     * Export search results to Excel/CSV
     */
    public function export(Request $request)
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:255',
            'type' => 'nullable|in:all,masuk,keluar',
            'export_format' => 'required|in:xlsx,csv,pdf',
        ] + $this->getFilterRules());

        $results = $this->searchService->search($validated);
        $formattedResults = $this->formatForDataTables($results, $request->input('q', ''));

        $format = $validated['export_format'];
        $filename = "search_results_" . date('Y-m-d_His') . ".{$format}";

        if ($format === 'pdf') {
            return $this->exportPdf($formattedResults, $filename);
        }

        return $this->exportExcel($formattedResults, $filename, $format);
    }

    /**
     * Export to Excel or CSV
     */
    private function exportExcel(array $data, string $filename, string $format)
    {
        // Prepare data for export
        $exportData = [];
        foreach ($data as $row) {
            $exportData[] = [
                'Jenis' => ucfirst($row['jenis']),
                'Agenda' => $row['agenda'],
                'Nomor Surat' => strip_tags($row['nomor_surat']),
                'Perihal' => strip_tags($row['perihal']),
                'Pengirim/Tujuan' => strip_tags($row['pengirim_tujuan']),
                'Tanggal' => $row['tanggal'],
                'Prioritas' => $row['prioritas'],
                'Status' => $row['status'],
                'Klasifikasi' => $row['klasifikasi'],
                'Unit' => $row['unit'],
            ];
        }

        if ($format === 'csv') {
            return response()->stream(function() use ($exportData) {
                $output = fopen('php://output', 'w');
                fputcsv($output, array_keys($exportData[0] ?? []));
                foreach ($exportData as $row) {
                    fputcsv($output, $row);
                }
                fclose($output);
            }, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        // For XLSX, you can use a package like maatwebsite/excel
        // This is a simplified version
        return response()->json([
            'message' => 'Excel export requires maatwebsite/excel package',
            'data' => $exportData,
        ]);
    }

    /**
     * Export to PDF
     */
    private function exportPdf(array $data, string $filename)
    {
        // Using a simple HTML to PDF approach
        // For production, use dompdf or snappy
        $html = view('search.export-pdf', compact('data'))->render();
        
        return response()->stream(function() use ($html) {
            echo $html;
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Get filter options (for AJAX loading)
     */
    public function getFilterOptions(Request $request)
    {
        $options = [];

        if ($request->has('units')) {
            $options['units'] = Unit::select('id', 'nama_unit')->orderBy('nama_unit')->get();
        }

        if ($request->has('klasifikasi')) {
            $options['klasifikasi'] = KlasifikasiArsip::select('id', 'kode', 'nama_klasifikasi')
                ->orderBy('kode')
                ->get();
        }

        if ($request->has('sifat')) {
            $options['sifat'] = SifatSurat::select('id', 'nama_sifat')->orderBy('nama_sifat')->get();
        }

        return response()->json(['success' => true, 'options' => $options]);
    }

    /**
     * Get validation rules for filters
     */
    private function getFilterRules(): array
    {
        return [
            'status' => 'nullable|array',
            'klasifikasi_id' => 'nullable|integer',
            'unit_id' => 'nullable|array',
            'sifat_id' => 'nullable|array',
            'prioritas' => 'nullable|array',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_sampai' => 'nullable|date|after_or_equal:tanggal_mulai',
        ];
    }
}
