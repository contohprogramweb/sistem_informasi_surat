<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use App\Models\NotificationPreference;
use App\Mail\SiapSmkNotificationMail;
use Illuminate\Support\Facades\Mail;

class SiapSmkNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $type;
    public $data;
    public $user;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $type, array $data = [])
    {
        $this->type = $type;
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        // Check if email should be sent
        $preference = NotificationPreference::getOrCreate($notifiable->id);
        
        if ($preference->shouldSendEmail($this->type)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $this->user = $notifiable;
        
        // Queue the custom mailable instead of using default MailMessage
        // This is handled in the queued listener
        return (new MailMessage)
            ->subject($this->getSubject())
            ->view('emails.notification', [
                'notificationData' => $this->toArray($notifiable),
                'user' => $notifiable,
            ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $titleMap = [
            'disposisi_baru' => 'Disposisi Baru',
            'disposisi_forward' => 'Disposisi Diteruskan',
            'disposisi_selesai' => 'Disposisi Selesai',
            'surat_disetujui' => 'Surat Disetujui',
            'surat_ditolak' => 'Surat Ditolak',
            'batas_waktu_h1' => 'Peringatan Batas Waktu (H-1)',
            'overdue' => 'Disposisi Overdue',
            'delegasi_aktif' => 'Delegasi Aktif',
        ];

        $iconMap = [
            'disposisi_baru' => '📋',
            'disposisi_forward' => '➡️',
            'disposisi_selesai' => '✅',
            'surat_disetujui' => '✔️',
            'surat_ditolak' => '❌',
            'batas_waktu_h1' => '⏰',
            'overdue' => '🚨',
            'delegasi_aktif' => '🔄',
        ];

        return [
            'type' => $this->type,
            'title' => $titleMap[$this->type] ?? 'Notifikasi',
            'icon' => $iconMap[$this->type] ?? '📢',
            'message' => $this->data['message'] ?? '',
            'action_url' => $this->data['action_url'] ?? null,
            'timestamp' => now()->format('d M Y H:i'),
            'disposisi_id' => $this->data['disposisi_id'] ?? null,
            'surat_id' => $this->data['surat_id'] ?? null,
        ];
    }

    /**
     * Get subject for email
     */
    private function getSubject(): string
    {
        return match($this->type) {
            'disposisi_baru' => 'Disposisi Baru - SIAP-SMK',
            'disposisi_forward' => 'Disposisi Diteruskan - SIAP-SMK',
            'disposisi_selesai' => 'Disposisi Selesai - SIAP-SMK',
            'surat_disetujui' => 'Surat Disetujui - SIAP-SMK',
            'surat_ditolak' => 'Surat Ditolak - SIAP-SMK',
            'batas_waktu_h1' => 'Peringatan: Batas Waktu Disposisi H-1',
            'overdue' => 'URGENT: Disposisi Overdue - SIAP-SMK',
            'delegasi_aktif' => 'Delegasi Aktif - SIAP-SMK',
            default => 'Notifikasi SIAP-SMK',
        };
    }
}
