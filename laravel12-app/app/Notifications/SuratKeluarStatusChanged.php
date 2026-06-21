<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\SuratKeluar;
use App\Enums\SuratKeluarStatus;

class SuratKeluarStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public SuratKeluar $surat,
        public SuratKeluarStatus $oldStatus,
        public SuratKeluarStatus $newStatus
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'surat_keluar_id' => $this->surat->id,
            'old_status' => $this->oldStatus->value,
            'new_status' => $this->newStatus->value,
            'old_status_label' => $this->oldStatus->label(),
            'new_status_label' => $this->newStatus->label(),
            'perihal' => $this->surat->perihal,
            'message' => "Surat keluar '{$this->surat->perihal}' telah berubah status dari {$this->oldStatus->label()} menjadi {$this->newStatus->label()}.",
        ];
    }
}
