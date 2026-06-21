<?php

namespace App\Services;

use App\Models\Disposisi;
use App\Models\Delegasi;
use App\Models\User;
use App\Notifications\DisposisiCreated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DisposisiService
{
    /**
     * Buat disposisi baru dengan pengecekan delegasi
     */
    public function createDisposisi(array $data): Disposisi
    {
        return DB::transaction(function () use ($data) {
            $keUserId = $data['ke_user_id'];
            
            // Cek apakah penerima memiliki delegasi aktif
            $delegasiAktif = Delegasi::where('user_id', $keUserId)
                ->active()
                ->first();
            
            if ($delegasiAktif && $delegasiAktif->isActiveNow()) {
                // Alihkan ke pengganti dengan keterangan "a.n."
                $actualReceiverId = $delegasiAktif->pengganti_user_id;
                $instruksi = "a.n. " . User::find($keUserId)->name . "\n\n" . $data['instruksi'];
            } else {
                $actualReceiverId = $keUserId;
                $instruksi = $data['instruksi'];
            }
            
            $disposisi = Disposisi::create([
                'surat_masuk_id' => $data['surat_masuk_id'],
                'dari_user_id' => Auth::id(),
                'ke_user_id' => $actualReceiverId,
                'instruksi' => $instruksi,
                'batas_waktu' => $data['batas_waktu'] ?? null,
                'prioritas' => $data['prioritas'] ?? 'Normal',
                'status' => 'Belum Dibaca',
                'parent_id' => $data['parent_id'] ?? null,
                'tembusan' => $data['tembusan'] ?? [],
            ]);
            
            // Kirim notifikasi ke penerima
            $penerima = User::find($actualReceiverId);
            if ($penerima) {
                $penerima->notify(new DisposisiCreated($disposisi));
            }
            
            // Kirim notifikasi ke tembusan jika ada
            if (!empty($data['tembusan'])) {
                foreach ($data['tembusan'] as $tembusanId) {
                    $userTembusan = User::find($tembusanId);
                    if ($userTembusan) {
                        $userTembusan->notify(new DisposisiCreated($disposisi, true));
                    }
                }
            }
            
            return $disposisi;
        });
    }
    
    /**
     * Forward disposisi (untuk Kabag ke staf di unit yang sama)
     */
    public function forwardDisposisi(Disposisi $parentDisposisi, array $data): Disposisi
    {
        return DB::transaction(function () use ($parentDisposisi, $data) {
            $keUserId = $data['ke_user_id'];
            
            // Validasi: penerima harus dalam unit yang sama
            $pemberiUnitId = $parentDisposisi->keUser->unit_id;
            $penerimaUnitId = User::find($keUserId)->unit_id;
            
            if ($pemberiUnitId !== $penerimaUnitId) {
                throw new \Exception('Penerima disposisi harus berada dalam unit yang sama');
            }
            
            // Cek delegasi untuk disposisi baru
            $delegasiAktif = Delegasi::where('user_id', $keUserId)
                ->active()
                ->first();
            
            if ($delegasiAktif && $delegasiAktif->isActiveNow()) {
                $actualReceiverId = $delegasiAktif->pengganti_user_id;
                $instruksi = "a.n. " . User::find($keUserId)->name . "\n\n" . $data['instruksi'];
            } else {
                $actualReceiverId = $keUserId;
                $instruksi = $data['instruksi'];
            }
            
            $disposisi = Disposisi::create([
                'surat_masuk_id' => $parentDisposisi->surat_masuk_id,
                'dari_user_id' => Auth::id(),
                'ke_user_id' => $actualReceiverId,
                'instruksi' => $instruksi,
                'batas_waktu' => $data['batas_waktu'] ?? null,
                'prioritas' => $data['prioritas'] ?? $parentDisposisi->prioritas,
                'status' => 'Belum Dibaca',
                'parent_id' => $parentDisposisi->id,
                'tembusan' => $data['tembusan'] ?? [],
            ]);
            
            // Kirim notifikasi
            $penerima = User::find($actualReceiverId);
            if ($penerima) {
                $penerima->notify(new DisposisiCreated($disposisi));
            }
            
            return $disposisi;
        });
    }
    
    /**
     * Mark disposisi sebagai sudah dibaca
     */
    public function markAsRead(Disposisi $disposisi): void
    {
        if (!$disposisi->read_at) {
            $disposisi->update([
                'read_at' => now(),
                'status' => 'Sudah Dibaca',
            ]);
        }
    }
    
    /**
     * Update status disposisi
     */
    public function updateStatus(Disposisi $disposisi, string $status, ?string $komentar = null, ?string $filePath = null): void
    {
        $updateData = [
            'status' => $status,
        ];
        
        if ($status === 'Selesai') {
            $updateData['komentar_selesai'] = $komentar;
            if ($filePath) {
                $updateData['file_tindak_lanjut'] = $filePath;
            }
        } elseif ($status === 'Belum Selesai') {
            $updateData['komentar_selesai'] = $komentar;
        } elseif ($status === 'Sedang Ditindaklanjuti') {
            // Reset komentar dan file jika mulai ditindaklanjuti lagi
            $updateData['komentar_selesai'] = null;
            $updateData['file_tindak_lanjut'] = null;
        }
        
        $disposisi->update($updateData);
    }
    
    /**
     * Cek apakah semua disposisi pada surat sudah selesai
     */
    public function allDisposisiSelesai(int $suratMasukId): bool
    {
        $totalDisposisi = Disposisi::where('surat_masuk_id', $suratMasukId)
            ->whereNull('parent_id') // Hanya root disposisi
            ->count();
        
        if ($totalDisposisi === 0) {
            return true; // Tidak ada disposisi, bisa diarsipkan
        }
        
        $selesaiCount = Disposisi::where('surat_masuk_id', $suratMasukId)
            ->where('status', 'Selesai')
            ->count();
        
        return $selesaiCount >= $totalDisposisi;
    }
    
    /**
     * Dapatkan timeline disposisi untuk surat
     */
    public function getTimeline(int $suratMasukId): array
    {
        $rootDisposisi = Disposisi::where('surat_masuk_id', $suratMasukId)
            ->whereNull('parent_id')
            ->with(['dariUser', 'keUser', 'children.fromUser', 'children.keUser'])
            ->get();
        
        $timeline = [];
        
        foreach ($rootDisposisi as $disposisi) {
            $timeline[] = $this->buildTimelineNode($disposisi, 0);
        }
        
        return $timeline;
    }
    
    private function buildTimelineNode(Disposisi $disposisi, int $level): array
    {
        $node = [
            'id' => $disposisi->id,
            'level' => $level,
            'dari' => $disposisi->dariUser->name ?? 'Unknown',
            'dari_avatar' => $disposisi->dariUser->profile_photo_url ?? null,
            'ke' => $disposisi->keUser->name ?? 'Unknown',
            'ke_avatar' => $disposisi->keUser->profile_photo_url ?? null,
            'instruksi' => $disposisi->instruksi,
            'status' => $disposisi->status,
            'status_color' => $disposisi->status_color,
            'prioritas' => $disposisi->prioritas,
            'batas_waktu' => $disposisi->batas_waktu?->format('d M Y'),
            'is_overdue' => $disposisi->isOverdue(),
            'read_at' => $disposisi->read_at?->diffForHumans(),
            'created_at' => $disposisi->created_at->diffForHumans(),
            'komentar_selesai' => $disposisi->komentar_selesai,
            'file_tindak_lanjut' => $disposisi->file_tindak_lanjut,
            'children' => [],
        ];
        
        foreach ($disposisi->children as $child) {
            $node['children'][] = $this->buildTimelineNode($child, $level + 1);
        }
        
        return $node;
    }
}
