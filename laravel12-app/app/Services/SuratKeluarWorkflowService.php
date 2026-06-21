<?php

namespace App\Services;

use App\Models\SuratKeluar;
use App\Models\SuratKeluarHistory;
use App\Enums\SuratKeluarStatus;
use App\Notifications\SuratKeluarStatusChanged;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class SuratKeluarWorkflowService
{
    public function transition(SuratKeluar $surat, SuratKeluarStatus $newStatus, ?string $notes = null): bool
    {
        $currentStatus = $surat->status;

        if (!$currentStatus->canTransitionTo($newStatus)) {
            throw new Exception("Transisi dari {$currentStatus->label()} ke {$newStatus->label()} tidak diizinkan.");
        }

        return DB::transaction(function () use ($surat, $currentStatus, $newStatus, $notes) {
            // Update status surat
            $oldStatus = $surat->status;
            $surat->status = $newStatus;
            
            // Set user yang terlibat berdasarkan transisi
            $this->assignUsersBasedOnTransition($surat, $newStatus);
            
            // Simpan catatan jika ada
            if ($notes) {
                if ($newStatus === SuratKeluarStatus::Disetujui || $newStatus === SuratKeluarStatus::Ditolak) {
                    $surat->catatan_review = $notes;
                }
                if ($newStatus === SuratKeluarStatus::Ditolak && $surat->alasan_tolak === null) {
                    $surat->alasan_tolak = $notes;
                }
            }
            
            $surat->save();

            // Catat history
            SuratKeluarHistory::create([
                'surat_keluar_id' => $surat->id,
                'from_status' => $oldStatus->value,
                'to_status' => $newStatus->value,
                'user_id' => Auth::id(),
                'notes' => $notes,
            ]);

            // Kirim notifikasi
            $this->sendNotification($surat, $oldStatus, $newStatus);

            return true;
        });
    }

    private function assignUsersBasedOnTransition(SuratKeluar $surat, SuratKeluarStatus $newStatus): void
    {
        switch ($newStatus) {
            case SuratKeluarStatus::Review:
                $surat->reviewer_id = Auth::id();
                break;
            case SuratKeluarStatus::Disetujui:
                $surat->approver_id = Auth::id();
                break;
            case SuratKeluarStatus::Tertandatangani:
                $surat->signed_by = Auth::id();
                break;
            default:
                break;
        }
    }

    private function sendNotification(SuratKeluar $surat, SuratKeluarStatus $oldStatus, SuratKeluarStatus $newStatus): void
    {
        $notifiables = [];

        // Notifikasi ke pembuat jika status berubah dari draft
        if ($oldStatus === SuratKeluarStatus::Draft && $newStatus !== SuratKeluarStatus::Ditolak) {
            $notifiables[] = $surat->creator;
        }

        // Notifikasi ke approver (Pimpinan) jika masuk review
        if ($newStatus === SuratKeluarStatus::Review) {
            // Dapatkan semua user dengan role pimpinan
            $pimpinan = \Spatie\Permission\Models\Role::findByName('pimpinan')->users;
            foreach ($pimpinan as $user) {
                $notifiables[] = $user;
            }
        }

        // Notifikasi ke TU jika disetujui
        if ($newStatus === SuratKeluarStatus::Disetujui) {
            $staffTu = \Spatie\Permission\Models\Role::findByName('staff_tu')->users;
            foreach ($staffTu as $user) {
                $notifiables[] = $user;
            }
        }

        // Notifikasi ke pembuat jika ditolak
        if ($newStatus === SuratKeluarStatus::Ditolak) {
            $notifiables[] = $surat->creator;
        }

        // Kirim notifikasi
        foreach (array_unique($notifiables) as $user) {
            if ($user) {
                $user->notify(new SuratKeluarStatusChanged($surat, $oldStatus, $newStatus));
            }
        }
    }

    public function canTransition(SuratKeluar $surat, SuratKeluarStatus $newStatus): bool
    {
        return $surat->status->canTransitionTo($newStatus);
    }

    public function getAvailableTransitions(SuratKeluar $surat): array
    {
        $available = [];
        foreach (SuratKeluarStatus::cases() as $status) {
            if ($this->canTransition($surat, $status)) {
                $available[] = $status;
            }
        }
        return $available;
    }
}
