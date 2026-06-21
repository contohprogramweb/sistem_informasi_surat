<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email_enabled',
        'frequency', // 'immediate', 'daily_digest'
        'types', // JSON: ['disposisi_baru', 'surat_disetujui', etc]
    ];

    protected $casts = [
        'email_enabled' => 'boolean',
        'types' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if user wants to receive specific notification type via email
     */
    public function shouldSendEmail(string $type): bool
    {
        if (!$this->email_enabled) {
            return false;
        }

        if (empty($this->types)) {
            // If no specific types set, send all
            return true;
        }

        return in_array($type, $this->types);
    }

    /**
     * Get or create preference for user
     */
    public static function getOrCreate(int $userId): self
    {
        return static::firstOrCreate(
            ['user_id' => $userId],
            [
                'email_enabled' => true,
                'frequency' => 'immediate',
                'types' => [],
            ]
        );
    }
}
