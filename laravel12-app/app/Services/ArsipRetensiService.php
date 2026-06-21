<?php

namespace App\Services;

use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use App\Models\KlasifikasiArsip;
use App\Models\BeritaAcaraPemusnahan;
use App\Models\BeritaAcaraDetail;
use App\Models\User;
use App\Models\Lampiran;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ArsipRetensiService
{
    /**
     * Archive surat masuk
     */
    public function archiveSuratMasuk(SuratMasuk $surat): array
    {
        DB::beginTransaction();
        
        try {
            // Validate can archive
            if (!$surat->canArchive()) {
                return [
                    'success' => false,
                    'message' => 'Surat belum dapat diarsipkan. Pastikan semua disposisi telah selesai.'
                ];
            }

            // Archive the surat
            $surat->archive();

            // Log activity
            Log::channel('audit')->info('Surat Masuk Diarsipkan', [
                'surat_masuk_id' => $surat->id,
                'nomor_surat' => $surat->nomor_surat,
                'tanggal_arsip' => $surat->tanggal_arsip,
                'tanggal_jatuh_aktif' => $surat->tanggal_jatuh_aktif,
                'tanggal_jatuh_inaktif' => $surat->tanggal_jatuh_inaktif,
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Surat berhasil diarsipkan',
                'data' => $surat
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal mengarsipkan surat masuk: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Gagal mengarsipkan surat: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Archive surat keluar
     */
    public function archiveSuratKeluar(SuratKeluar $surat): array
    {
        DB::beginTransaction();
        
        try {
            // Validate can archive
            if (!$surat->canArchive()) {
                return [
                    'success' => false,
                    'message' => 'Surat belum dapat diarsipkan. Status harus "Terkirim".'
                ];
            }

            // Archive the surat
            $surat->archive();

            // Log activity
            Log::channel('audit')->info('Surat Keluar Diarsipkan', [
                'surat_keluar_id' => $surat->id,
                'nomor_surat' => $surat->nomor_surat_final,
                'tanggal_arsip' => $surat->tanggal_arsip,
                'tanggal_jatuh_aktif' => $surat->tanggal_jatuh_aktif,
                'tanggal_jatuh_inaktif' => $surat->tanggal_jatuh_inaktif,
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Surat berhasil diarsipkan',
                'data' => $surat
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal mengarsipkan surat keluar: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Gagal mengarsipkan surat: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get arsip that will fall due within X months
     */
    public function getJatuhTempoArsip(int $monthsAhead, string $type = 'all'): array
    {
        $endDate = now()->addMonths($monthsAhead);
        $results = [];

        if ($type === 'all' || $type === 'aktif') {
            // Surat Masuk - jatuh tempo aktif
            $suratMasukAktif = SuratMasuk::where('status_arsip', 'aktif')
                ->whereNotNull('tanggal_jatuh_aktif')
                ->whereBetween('tanggal_jatuh_aktif', [now(), $endDate])
                ->with(['klasifikasi', 'unitTujuan'])
                ->get();

            foreach ($suratMasukAktif as $surat) {
                $results[] = [
                    'type' => 'surat_masuk',
                    'model' => $surat,
                    'jatuh_tempo_type' => 'aktif',
                    'tanggal_jatuh_tempo' => $surat->tanggal_jatuh_aktif,
                    'sisa_bulan' => now()->diffInMonths($surat->tanggal_jatuh_aktif, false),
                ];
            }

            // Surat Keluar - jatuh tempo aktif
            $suratKeluarAktif = SuratKeluar::where('status_arsip', 'aktif')
                ->whereNotNull('tanggal_jatuh_aktif')
                ->whereBetween('tanggal_jatuh_aktif', [now(), $endDate])
                ->with(['klasifikasi', 'unitPembuat'])
                ->get();

            foreach ($suratKeluarAktif as $surat) {
                $results[] = [
                    'type' => 'surat_keluar',
                    'model' => $surat,
                    'jatuh_tempo_type' => 'aktif',
                    'tanggal_jatuh_tempo' => $surat->tanggal_jatuh_aktif,
                    'sisa_bulan' => now()->diffInMonths($surat->tanggal_jatuh_aktif, false),
                ];
            }
        }

        if ($type === 'all' || $type === 'inaktif') {
            // Surat Masuk - jatuh tempo inaktif
            $suratMasukInaktif = SuratMasuk::where('status_arsip', 'inaktif')
                ->whereNotNull('tanggal_jatuh_inaktif')
                ->whereBetween('tanggal_jatuh_inaktif', [now(), $endDate])
                ->with(['klasifikasi'])
                ->get();

            foreach ($suratMasukInaktif as $surat) {
                $results[] = [
                    'type' => 'surat_masuk',
                    'model' => $surat,
                    'jatuh_tempo_type' => 'inaktif',
                    'tanggal_jatuh_tempo' => $surat->tanggal_jatuh_inaktif,
                    'sisa_bulan' => now()->diffInMonths($surat->tanggal_jatuh_inaktif, false),
                ];
            }

            // Surat Keluar - jatuh tempo inaktif
            $suratKeluarInaktif = SuratKeluar::where('status_arsip', 'inaktif')
                ->whereNotNull('tanggal_jatuh_inaktif')
                ->whereBetween('tanggal_jatuh_inaktif', [now(), $endDate])
                ->with(['klasifikasi'])
                ->get();

            foreach ($suratKeluarInaktif as $surat) {
                $results[] = [
                    'type' => 'surat_keluar',
                    'model' => $surat,
                    'jatuh_tempo_type' => 'inaktif',
                    'tanggal_jatuh_tempo' => $surat->tanggal_jatuh_inaktif,
                    'sisa_bulan' => now()->diffInMonths($surat->tanggal_jatuh_inaktif, false),
                ];
            }
        }

        // Sort by tanggal_jatuh_tempo
        usort($results, function($a, $b) {
            return $a['tanggal_jatuh_tempo']->timestamp <=> $b['tanggal_jatuh_tempo']->timestamp;
        });

        return $results;
    }

    /**
     * Create berita acara pemusnahan
     */
    public function createBeritaAcara(array $data, array $arsipList): array
    {
        DB::beginTransaction();
        
        try {
            // Create berita acara
            $beritaAcara = BeritaAcaraPemusnahan::create([
                'nomor_berita_acara' => BeritaAcaraPemusnahan::generateNomorBeritaAcara(),
                'tanggal_berita_acara' => $data['tanggal_berita_acara'] ?? now(),
                'keterangan' => $data['keterangan'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Add arsip to berita acara
            foreach ($arsipList as $arsipItem) {
                $arsip = null;
                
                if ($arsipItem['type'] === 'surat_masuk') {
                    $arsip = SuratMasuk::findOrFail($arsipItem['id']);
                } elseif ($arsipItem['type'] === 'surat_keluar') {
                    $arsip = SuratKeluar::findOrFail($arsipItem['id']);
                }

                if ($arsip) {
                    $beritaAcara->addArsip($arsip);
                    
                    // Mark arsip as destroyed
                    $arsip->markAsDestroyed(auth()->user());
                }
            }

            // Log activity
            Log::channel('audit')->info('Berita Acara Pemusnahan Dibuat', [
                'berita_acara_id' => $beritaAcara->id,
                'nomor_berita_acara' => $beritaAcara->nomor_berita_acara,
                'total_arsip' => count($arsipList),
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Berita acara pemusnahan berhasil dibuat',
                'data' => $beritaAcara
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal membuat berita acara pemusnahan: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Gagal membuat berita acara: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Soft delete surat with 30-day retention
     */
    public function softDeleteSurat($surat, string $reason): array
    {
        DB::beginTransaction();
        
        try {
            $user = auth()->user();
            
            // Check if already archived or destroyed
            if ($surat->isReadOnly()) {
                return [
                    'success' => false,
                    'message' => 'Arsip yang sudah diarsipkan atau dimusnahkan tidak dapat dihapus.'
                ];
            }

            // Soft delete
            $surat->softDeleteWithReason($reason, $user);

            // Log activity
            Log::channel('audit')->info('Surat Dihapus Sementara', [
                'surat_id' => $surat->id,
                'surat_type' => get_class($surat),
                'nomor_surat' => $surat->nomor_surat ?? $surat->nomor_surat_final,
                'alasan' => $reason,
                'deleted_until' => $surat->deleted_until,
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Surat berhasil dihapus. Dapat dipulihkan dalam 30 hari.'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menghapus surat: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Gagal menghapus surat: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Restore soft deleted surat
     */
    public function restoreSurat($surat): array
    {
        DB::beginTransaction();
        
        try {
            if (!$surat->canBeRestored()) {
                return [
                    'success' => false,
                    'message' => 'Surat tidak dapat dipulihkan. Masa pemulihan telah berakhir.'
                ];
            }

            $surat->restoreFromSoftDelete();

            // Log activity
            Log::channel('audit')->info('Surat Dipulihkan', [
                'surat_id' => $surat->id,
                'surat_type' => get_class($surat),
                'nomor_surat' => $surat->nomor_surat ?? $surat->nomor_surat_final,
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Surat berhasil dipulihkan'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memulihkan surat: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Gagal memulihkan surat: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Permanently delete surat after 30 days
     */
    public function permanentDeleteExpiredSurat(): int
    {
        $expiredSuratMasuk = SuratMasuk::whereNotNull('deleted_until')
            ->where('deleted_until', '<=', now())
            ->get();

        $deletedCount = 0;

        foreach ($expiredSuratMasuk as $surat) {
            DB::beginTransaction();
            
            try {
                // Delete physical attachments
                $lampiran = Lampiran::where('attachable_type', SuratMasuk::class)
                    ->where('attachable_id', $surat->id)
                    ->get();

                foreach ($lampiran as $lamp) {
                    if (Storage::exists($lamp->filename)) {
                        Storage::delete($lamp->filename);
                    }
                    $lamp->forceDelete();
                }

                // Log before permanent delete
                Log::channel('audit')->info('Surat Masuk Dihapus Permanen', [
                    'surat_masuk_id' => $surat->id,
                    'nomor_surat' => $surat->nomor_surat,
                    'alasan_hapus' => $surat->alasan_hapus,
                ]);

                // Force delete
                $surat->forceDelete();
                $deletedCount++;

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Gagal menghapus permanen surat masuk: ' . $e->getMessage());
            }
        }

        // Same for Surat Keluar
        $expiredSuratKeluar = SuratKeluar::whereNotNull('deleted_until')
            ->where('deleted_until', '<=', now())
            ->get();

        foreach ($expiredSuratKeluar as $surat) {
            DB::beginTransaction();
            
            try {
                // Delete physical attachments
                $lampiran = Lampiran::where('attachable_type', SuratKeluar::class)
                    ->where('attachable_id', $surat->id)
                    ->get();

                foreach ($lampiran as $lamp) {
                    if (Storage::exists($lamp->filename)) {
                        Storage::delete($lamp->filename);
                    }
                    $lamp->forceDelete();
                }

                // Log before permanent delete
                Log::channel('audit')->info('Surat Keluar Dihapus Permanen', [
                    'surat_keluar_id' => $surat->id,
                    'nomor_surat' => $surat->nomor_surat_final,
                    'alasan_hapus' => $surat->alasan_hapus,
                ]);

                // Force delete
                $surat->forceDelete();
                $deletedCount++;

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Gagal menghapus permanen surat keluar: ' . $e->getMessage());
            }
        }

        return $deletedCount;
    }

    /**
     * Send notifications for upcoming retention dates
     */
    public function sendRetentionNotifications(int $monthsAhead = 3): int
    {
        $jatuhTempoList = $this->getJatuhTempoArsip($monthsAhead);
        $notificationCount = 0;

        foreach ($jatuhTempoList as $item) {
            $arsip = $item['model'];
            $type = $item['jatuh_tempo_type'] === 'aktif' ? 'jatuh_tempo_aktif' : 'jatuh_tempo_inaktif';

            // Check if notification already sent
            $existingNotification = \App\Models\ArsipNotification::where('arsip_id', $arsip->id)
                ->where('arsip_type', get_class($arsip))
                ->where('type', $type)
                ->where('bulan_sebelumnya', $item['sisa_bulan'])
                ->first();

            if (!$existingNotification) {
                \App\Models\ArsipNotification::create([
                    'arsip_id' => $arsip->id,
                    'arsip_type' => get_class($arsip),
                    'type' => $type,
                    'bulan_sebelumnya' => $item['sisa_bulan'],
                    'is_read' => false,
                    'sent_at' => now(),
                ]);

                $notificationCount++;
            }
        }

        return $notificationCount;
    }
}
