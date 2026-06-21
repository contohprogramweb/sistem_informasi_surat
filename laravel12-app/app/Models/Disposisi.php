<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Disposisi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'disposisi';

    protected $fillable = [
        'surat_masuk_id',
        'dari_user_id',
        'ke_user_id',
        'instruksi',
        'batas_waktu',
        'prioritas',
        'status',
        'parent_id',
        'tembusan',
        'read_at',
        'komentar_selesai',
        'file_tindak_lanjut',
    ];

    protected $casts = [
        'tembusan' => 'array',
        'batas_waktu' => 'date',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function suratMasuk(): BelongsTo
    {
        return $this->belongsTo(SuratMasuk::class);
    }

    public function dariUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dari_user_id');
    }

    public function keUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ke_user_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Disposisi::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Disposisi::class, 'parent_id');
    }

    public function getAllChildren()
    {
        $children = $this->children;
        foreach ($children as $child) {
            $children = $children->merge($child->getAllChildren());
        }
        return $children;
    }

    public function scopeBelumDibaca($query)
    {
        return $query->where('status', 'Belum Dibaca');
    }

    public function scopeSudahDibaca($query)
    {
        return $query->where('status', 'Sudah Dibaca');
    }

    public function scopeSedangDitindaklanjuti($query)
    {
        return $query->where('status', 'Sedang Ditindaklanjuti');
    }

    public function scopeSelesai($query)
    {
        return $query->where('status', 'Selesai');
    }

    public function isOverdue(): bool
    {
        if (!$this->batas_waktu || in_array($this->status, ['Selesai'])) {
            return false;
        }
        return now()->gt($this->batas_waktu);
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'Belum Dibaca' => 'secondary',
            'Sudah Dibaca' => 'primary',
            'Sedang Ditindaklanjuti' => 'warning',
            'Selesai' => 'success',
            'Belum Selesai' => 'danger',
            default => 'secondary',
        };
    }
}
