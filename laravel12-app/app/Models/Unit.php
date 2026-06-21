<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['kode_unit', 'nama_unit', 'deskripsi'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function suratMasuk(): HasMany
    {
        return $this->hasMany(SuratMasuk::class);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where('nama_unit', 'like', "%{$term}%")
                     ->orWhere('kode_unit', 'like', "%{$term}%");
    }
}
