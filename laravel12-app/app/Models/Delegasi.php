<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delegasi extends Model
{
    protected $fillable = [
        'user_id',
        'pengganti_user_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function penggantiUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengganti_user_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->whereDate('tanggal_mulai', '<=', now())
                     ->whereDate('tanggal_selesai', '>=', now());
    }

    public function isActiveNow(): bool
    {
        if (!$this->is_active) {
            return false;
        }
        
        $today = now()->toDateString();
        return $today >= $this->tanggal_mulai->toDateString() 
            && $today <= $this->tanggal_selesai->toDateString();
    }
}
