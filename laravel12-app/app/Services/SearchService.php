<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\SuratMasuk;
use App\Models\SuratKeluar;

class SearchService
{
    /**
     * Perform global search across Surat Masuk and Surat Keluar
     * using MySQL FULLTEXT search with MATCH/AGAINST
     */
    public function search(array $params): array
    {
        $query = $params['q'] ?? '';
        $type = $params['type'] ?? 'all'; // all, masuk, keluar
        $sortBy = $params['sort_by'] ?? 'relevance';
        $sortOrder = $params['sort_order'] ?? 'desc';
        
        $results = [
            'surat_masuk' => [],
            'surat_keluar' => [],
            'total' => 0,
            'query' => $query,
        ];

        if (empty($query)) {
            return $results;
        }

        // Prepare search terms for FULLTEXT
        $searchTerms = $this->prepareSearchTerms($query);

        // Search Surat Masuk
        if ($type === 'all' || $type === 'masuk') {
            $results['surat_masuk'] = $this->searchSuratMasuk($searchTerms, $params);
        }

        // Search Surat Keluar
        if ($type === 'all' || $type === 'keluar') {
            $results['surat_keluar'] = $this->searchSuratKeluar($searchTerms, $params);
        }

        // Calculate total
        $results['total'] = count($results['surat_masuk']) + count($results['surat_keluar']);

        // Sort results
        $results = $this->sortResults($results, $sortBy, $sortOrder);

        return $results;
    }

    /**
     * Prepare search terms for FULLTEXT search
     * Supports boolean mode operators: +, -, *, "phrase"
     */
    private function prepareSearchTerms(string $query): string
    {
        // Remove special characters except FULLTEXT operators
        $cleaned = preg_replace('/[<>~()]/', '', $query);
        
        // Add wildcard for partial matching
        $words = explode(' ', trim($cleaned));
        $processed = [];
        
        foreach ($words as $word) {
            if (!empty($word)) {
                // Add + for required words, * for wildcard
                $processed[] = '+' . $word . '*';
            }
        }
        
        return implode(' ', $processed);
    }

    /**
     * Search in Surat Masuk with FULLTEXT
     */
    private function searchSuratMasuk(string $searchTerms, array $params): array
    {
        $suratMasukQuery = SuratMasuk::query();

        // Apply filters
        $suratMasukQuery = $this->applySuratMasukFilters($suratMasukQuery, $params);

        // FULLTEXT search using MATCH/AGAINST in Boolean Mode
        $suratMasukQuery->where(function($q) use ($searchTerms) {
            // Check if FULLTEXT index exists, fallback to LIKE if not
            try {
                $q->whereRaw("MATCH(agenda, nomor_surat, perihal, pengirim, indeks) AGAINST(? IN BOOLEAN MODE)", [$searchTerms]);
            } catch (\Exception $e) {
                // Fallback to LIKE search if FULLTEXT index doesn't exist
                $terms = str_replace(['+', '*'], '', $searchTerms);
                $q->where(function($subQ) use ($terms) {
                    $subQ->where('agenda', 'like', "%{$terms}%")
                         ->orWhere('nomor_surat', 'like', "%{$terms}%")
                         ->orWhere('perihal', 'like', "%{$terms}%")
                         ->orWhere('pengirim', 'like', "%{$terms}%");
                    
                    // Search in JSON indeks field
                    if (config('database.default') === 'mysql') {
                        $subQ->orWhereRaw("JSON_SEARCH(indeks, 'one', ?) IS NOT NULL", ["%{$terms}%"]);
                    }
                });
            }
        });

        // Add relevance score for sorting
        if (config('database.default') === 'mysql') {
            $suratMasukQuery->addSelect(DB::raw("
                (MATCH(agenda, nomor_surat, perihal, pengirim, indeks) AGAINST(? IN NATURAL LANGUAGE MODE)) as relevance_score
            ", [$searchTerms]));
        }

        return $suratMasukQuery->limit(50)->get()->toArray();
    }

    /**
     * Search in Surat Keluar with FULLTEXT
     */
    private function searchSuratKeluar(string $searchTerms, array $params): array
    {
        $suratKeluarQuery = SuratKeluar::query();

        // Apply filters
        $suratKeluarQuery = $this->applySuratKeluarFilters($suratKeluarQuery, $params);

        // FULLTEXT search
        $suratKeluarQuery->where(function($q) use ($searchTerms) {
            try {
                $q->whereRaw("MATCH(nomor_surat_final, perihal, tujuan, isi_ringkas) AGAINST(? IN BOOLEAN MODE)", [$searchTerms]);
            } catch (\Exception $e) {
                // Fallback to LIKE search
                $terms = str_replace(['+', '*'], '', $searchTerms);
                $q->where(function($subQ) use ($terms) {
                    $subQ->where('nomor_surat_final', 'like', "%{$terms}%")
                         ->orWhere('perihal', 'like', "%{$terms}%")
                         ->orWhere('tujuan', 'like', "%{$terms}%")
                         ->orWhere('isi_ringkas', 'like', "%{$terms}%");
                });
            }
        });

        // Add relevance score
        if (config('database.default') === 'mysql') {
            $suratKeluarQuery->addSelect(DB::raw("
                (MATCH(nomor_surat_final, perihal, tujuan, isi_ringkas) AGAINST(? IN NATURAL LANGUAGE MODE)) as relevance_score
            ", [$searchTerms]));
        }

        return $suratKeluarQuery->limit(50)->get()->toArray();
    }

    /**
     * Apply filters to Surat Masuk query
     */
    private function applySuratMasukFilters($query, array $params)
    {
        // Jenis surat (already filtered by model)
        
        // Status
        if (!empty($params['status'])) {
            $query->whereIn('status', (array)$params['status']);
        }

        // Date range
        if (!empty($params['tanggal_mulai'])) {
            $query->whereDate('tanggal_terima', '>=', $params['tanggal_mulai']);
        }
        if (!empty($params['tanggal_sampai'])) {
            $query->whereDate('tanggal_terima', '<=', $params['tanggal_sampai']);
        }

        // Klasifikasi
        if (!empty($params['klasifikasi_id'])) {
            $query->where('klasifikasi_id', $params['klasifikasi_id']);
        }

        // Unit (multi-select)
        if (!empty($params['unit_id'])) {
            $unitIds = (array)$params['unit_id'];
            $query->whereHas('unitTujuan', function($q) use ($unitIds) {
                $q->whereIn('units.id', $unitIds);
            });
        }

        // Sifat (multi-select)
        if (!empty($params['sifat_id'])) {
            $query->whereIn('sifat_id', (array)$params['sifat_id']);
        }

        // Prioritas (multi-select)
        if (!empty($params['prioritas'])) {
            $query->whereIn('prioritas', (array)$params['prioritas']);
        }

        // Surat Terkait
        if (!empty($params['surat_terkait'])) {
            $query->where('id', '!=', $params['surat_terkait']);
        }

        return $query;
    }

    /**
     * Apply filters to Surat Keluar query
     */
    private function applySuratKeluarFilters($query, array $params)
    {
        // Status
        if (!empty($params['status'])) {
            $query->whereIn('status', (array)$params['status']);
        }

        // Date range
        if (!empty($params['tanggal_mulai'])) {
            $query->whereDate('tanggal_surat_final', '>=', $params['tanggal_mulai']);
        }
        if (!empty($params['tanggal_sampai'])) {
            $query->whereDate('tanggal_surat_final', '<=', $params['tanggal_sampai']);
        }

        // Klasifikasi
        if (!empty($params['klasifikasi_id'])) {
            $query->where('klasifikasi_id', $params['klasifikasi_id']);
        }

        // Unit (multi-select)
        if (!empty($params['unit_id'])) {
            $query->whereIn('unit_pembuat_id', (array)$params['unit_id']);
        }

        // Sifat (multi-select)
        if (!empty($params['sifat_id'])) {
            $query->whereIn('sifat_id', (array)$params['sifat_id']);
        }

        // Surat Terkait
        if (!empty($params['surat_terkait'])) {
            $query->where('id', '!=', $params['surat_terkait']);
        }

        return $query;
    }

    /**
     * Sort search results
     */
    private function sortResults(array $results, string $sortBy, string $sortOrder): array
    {
        $direction = strtolower($sortOrder) === 'asc' ? 1 : -1;

        // Sort Surat Masuk
        if (!empty($results['surat_masuk'])) {
            usort($results['surat_masuk'], function($a, $b) use ($sortBy, $direction) {
                return $this->compareItems($a, $b, $sortBy) * $direction;
            });
        }

        // Sort Surat Keluar
        if (!empty($results['surat_keluar'])) {
            usort($results['surat_keluar'], function($a, $b) use ($sortBy, $direction) {
                return $this->compareItems($a, $b, $sortBy) * $direction;
            });
        }

        return $results;
    }

    /**
     * Compare two items for sorting
     */
    private function compareItems($a, $b, string $sortBy): int
    {
        switch ($sortBy) {
            case 'relevance':
                $scoreA = $a['relevance_score'] ?? 0;
                $scoreB = $b['relevance_score'] ?? 0;
                return $scoreA <=> $scoreB;

            case 'tanggal':
                $dateA = $a['tanggal_terima'] ?? $a['tanggal_surat_final'] ?? null;
                $dateB = $b['tanggal_terima'] ?? $b['tanggal_surat_final'] ?? null;
                if (!$dateA || !$dateB) return 0;
                return strtotime($dateA) <=> strtotime($dateB);

            case 'prioritas':
                $priorityOrder = ['Segera' => 4, 'Tinggi' => 3, 'Normal' => 2, 'Rendah' => 1];
                $prioA = $priorityOrder[$a['prioritas'] ?? 'Normal'] ?? 2;
                $prioB = $priorityOrder[$b['prioritas'] ?? 'Normal'] ?? 2;
                return $prioA <=> $prioB;

            case 'agenda':
                return ($a['agenda'] ?? '') <=> ($b['agenda'] ?? '');

            default:
                return 0;
        }
    }

    /**
     * Highlight search terms in text
     */
    public function highlightTerms(string $text, string $query): string
    {
        if (empty($query) || empty($text)) {
            return $text;
        }

        $terms = explode(' ', preg_quote($query, '/'));
        $pattern = '/(' . implode('|', $terms) . ')/i';
        $replacement = '<mark class="bg-warning text-dark">$1</mark>';

        return preg_replace($pattern, $replacement, $text);
    }

    /**
     * Get searchable fields info
     */
    public function getSearchableFields(): array
    {
        return [
            'surat_masuk' => [
                'agenda',
                'nomor_surat',
                'perihal',
                'pengirim',
                'indeks',
            ],
            'surat_keluar' => [
                'nomor_surat_final',
                'perihal',
                'tujuan',
                'isi_ringkas',
            ],
        ];
    }

    /**
     * Create FULLTEXT indexes (run once during migration/setup)
     */
    public function createFullTextIndexes(): void
    {
        if (config('database.default') !== 'mysql') {
            return;
        }

        try {
            // Surat Masuk FULLTEXT index
            DB::statement("ALTER TABLE surat_masuk ADD FULLTEXT INDEX ft_surat_masuk_search (agenda, nomor_surat, perihal, pengirim, indeks)");
            
            // Surat Keluar FULLTEXT index
            DB::statement("ALTER TABLE surat_keluar ADD FULLTEXT INDEX ft_surat_keluar_search (nomor_surat_final, perihal, tujuan, isi_ringkas)");
        } catch (\Exception $e) {
            // Indexes might already exist
            \Log::info('FULLTEXT indexes creation skipped: ' . $e->getMessage());
        }
    }
}
