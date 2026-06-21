<?php

namespace App\Notifications;

use App\Models\Disposisi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class DisposisiCreated extends Notification implements ShouldQueue
{
    use Queueable;

    protected Disposisi $disposisi;
    protected bool $isTembusan;

    /**
     * Create a new notification instance.
     */
    public function __construct(Disposisi $disposisi, bool $isTembusan = false)
    {
        $this->disposisi = $disposisi;
        $this->isTembusan = $isTembusan;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->isTembusan 
            ? 'Tembusan Disposisi Surat Masuk' 
            : 'Disposisi Surat Masuk Baru';
        
        return (new MailMessage)
            ->subject($subject)
            ->greeting('Halo ' . $notifiable->name . '!')
            ->line($this->isTembusan 
                ? 'Anda menerima tembusan disposisi surat masuk.'
                : 'Anda menerima disposisi surat masuk baru.')
            ->line('Surat dari: ' . $this->disposisi->dariUser->name)
            ->line('Instruksi: ' . substr($this->disposisi->instruksi, 0, 100) . '...')
            ->line('Prioritas: ' . $this->disposisi->prioritas)
            ->action('Lihat Disposisi', url('/disposisi/' . $this->disposisi->id))
            ->line('Terima kasih telah menggunakan SIAP-SMK.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'disposisi_id' => $this->disposisi->id,
            'surat_masuk_id' => $this->disposisi->surat_masuk_id,
            'dari_user_id' => $this->disposisi->dari_user_id,
            'dari_user_name' => $this->disposisi->dariUser->name ?? 'Unknown',
            'type' => $this->isTembusan ? 'tembusan' : 'disposisi',
            'message' => $this->isTembusan 
                ? 'Anda menerima tembusan disposisi dari ' . $this->disposisi->dariUser->name
                : 'Anda menerima disposisi baru dari ' . $this->disposisi->dariUser->name,
            'prioritas' => $this->disposisi->prioritas,
            'created_at' => $this->disposisi->created_at->toIso8601String(),
        ];
    }
}
