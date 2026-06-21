<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CatatanPribadi extends Model
{
    protected $fillable = [
        'user_id',
        'catatable_type',
        'catatable_id',
        'isi',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the parent catatable model (surat masuk or surat keluar)
     */
    public function catatable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user that owns the catatan
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
