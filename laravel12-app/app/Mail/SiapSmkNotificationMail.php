<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SiapSmkNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $notificationData;
    public $user;

    /**
     * Create a new message instance.
     */
    public function __construct($notificationData, $user)
    {
        $this->notificationData = $notificationData;
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $type = $this->notificationData['type'] ?? 'notification';
        $subject = match($type) {
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

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.notification',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
