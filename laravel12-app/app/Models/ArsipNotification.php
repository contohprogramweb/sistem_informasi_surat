<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ArsipNotification extends Model
{
    protected $table = 'arsip_notifications';

    protected $fillable = [
        'arsip_id',
        'arsip_type',
        'type',
        'bulan_sebelumnya',
        'is_read',
        'sent_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'sent_at' => 'datetime',
    ];

    public function arsip(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }

    /**
     * Get notification type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'jatuh_tempo_aktif' => 'Jatuh Tempo Aktif',
            'jatuh_tempo_inaktif' => 'Jatuh Tempo Inaktif',
            'reminder_pemusnahan' => 'Reminder Pemusnahan',
            default => $this->type,
        };
    }

    /**
     * Get notification message
     */
    public function getMessageAttribute(): string
    {
        $arsip = $this->arsip;
        if (!$arsip) {
            return 'Arsip tidak ditemukan';
        }

        return match($this->type) {
            'jatuh_tempo_aktif' => "Arsip {$arsip->nomor_surat} akan jatuh tempo aktif dalam {$this->bulan_sebelumnya} bulan",
            'jatuh_tempo_inaktif' => "Arsip {$arsip->nomor_surat} akan jatuh tempo inaktif dalam {$this->bulan_sebelumnya} bulan",
            'reminder_pemusnahan' => "Arsip {$arsip->nomor_surat} siap untuk dimusnahkan ({$this->bulan_sebelumnya} bulan sebelum pemusnahan)",
            default => 'Notifikasi arsip',
        };
    }
}
