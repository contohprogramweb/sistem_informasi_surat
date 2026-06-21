<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TteLog extends Model
{
    protected $fillable = [
        'user_id',
        'surat_keluar_id',
        'hash_file',
        'pdf_path',
        'position_x',
        'position_y',
        'scale',
        'ip_address',
        'error_message',
        'success',
    ];

    protected $casts = [
        'position_x' => 'integer',
        'position_y' => 'integer',
        'scale' => 'decimal:2',
        'success' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function suratKeluar(): BelongsTo
    {
        return $this->belongsTo(SuratKeluar::class);
    }
}
