<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TteSignature extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'encrypted_path',
        'original_filename',
        'mime_type',
        'file_size',
        'is_active',
        'expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
        'file_size' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if signature is still valid
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Get the decrypted file path
     */
    public function getDecryptedPath(): ?string
    {
        if (!file_exists($this->encrypted_path)) {
            return null;
        }

        $encryptedContent = file_get_contents($this->encrypted_path);
        $decrypted = \Illuminate\Support\Facades\Crypt::decryptString($encryptedContent);

        // Create temporary file with decrypted content
        $tempPath = storage_path('app/temp/' . uniqid() . '_' . $this->original_filename);
        
        // Ensure temp directory exists
        if (!file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        // Decode base64 and save
        file_put_contents($tempPath, base64_decode($decrypted));

        return $tempPath;
    }
}
