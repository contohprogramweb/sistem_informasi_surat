<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KlasifikasiArsip extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'klasifikasi_arsip';

    protected $fillable = [
        'kode', 'nama', 'parent_id',
        'retensi_aktif', 'retensi_inaktif'
    ];

    protected $casts = [
        'retensi_aktif' => 'integer',
        'retensi_inaktif' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(KlasifikasiArsip::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(KlasifikasiArsip::class, 'parent_id');
    }

    public function suratMasuk(): HasMany
    {
        return $this->hasMany(SuratMasuk::class, 'klasifikasi_id');
    }

    public function getAllChildren()
    {
        $children = $this->children;
        foreach ($children as $child) {
            $children = $children->merge($child->getAllChildren());
        }
        return $children;
    }

    public function scopeSearch($query, $term)
    {
        return $query->where('nama', 'like', "%{$term}%")
                     ->orWhere('kode', 'like', "%{$term}%");
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }
}
