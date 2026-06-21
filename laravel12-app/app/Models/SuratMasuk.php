<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class SuratMasuk extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'agenda',
        'tanggal_terima',
        'cara_terima',
        'penerima_fisik',
        'nomor_surat',
        'tanggal_surat',
        'pengirim',
        'perihal',
        'ringkasan',
        'klasifikasi_id',
        'sifat_id',
        'prioritas',
        'indeks',
        'tidak_perlu_disposisi',
        'status',
        'deleted_until',
    ];

    protected $casts = [
        'tanggal_terima' => 'date',
        'tanggal_surat' => 'date',
        'indeks' => 'array',
        'tidak_perlu_disposisi' => 'boolean',
        'deleted_until' => 'datetime',
    ];

    public function klasifikasi(): BelongsTo
    {
        return $this->belongsTo(KlasifikasiArsip::class, 'klasifikasi_id');
    }

    public function sifat(): BelongsTo
    {
        return $this->belongsTo(SifatSurat::class, 'sifat_id');
    }

    public function unitTujuan(): BelongsToMany
    {
        return $this->belongsToMany(Unit::class, 'surat_masuk_unit_tujuan')
                    ->withTimestamps();
    }

    public function disposisi(): HasMany
    {
        return $this->hasMany(Disposisi::class, 'surat_id');
    }

    public function lampiran(): MorphMany
    {
        return $this->morphMany(Lampiran::class, 'lampirable');
    }

    public function catatanPribadi(): MorphMany
    {
        return $this->morphMany(CatatanPribadi::class, 'catatable');
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('nomor_surat', 'like', "%{$term}%")
              ->orWhere('pengirim', 'like', "%{$term}%")
              ->orWhere('perihal', 'like', "%{$term}%");
        });
    }

    public function scopeFilter($query, $filters)
    {
        if (isset($filters['tanggal_mulai'])) {
            $query->whereDate('tanggal_terima', '>=', $filters['tanggal_mulai']);
        }
        if (isset($filters['tanggal_sampai'])) {
            $query->whereDate('tanggal_terima', '<=', $filters['tanggal_sampai']);
        }
        if (isset($filters['klasifikasi_id'])) {
            $query->where('klasifikasi_id', $filters['klasifikasi_id']);
        }
        if (isset($filters['unit_id'])) {
            $query->whereHas('unitTujuan', function($q) use ($filters) {
                $q->where('units.id', $filters['unit_id']);
            });
        }
        if (isset($filters['sifat_id'])) {
            $query->where('sifat_id', $filters['sifat_id']);
        }
        if (isset($filters['prioritas'])) {
            $query->where('prioritas', $filters['prioritas']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        return $query;
    }

    public function getPrioritasBadgeClassAttribute()
    {
        return match($this->prioritas) {
            'Segera' => 'bg-danger',
            'Tinggi' => 'bg-warning text-dark',
            'Normal' => 'bg-info text-dark',
            'Rendah' => 'bg-secondary',
            default => 'bg-secondary',
        };
    }

    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'Aktif' => 'bg-success',
            'Diarsipkan' => 'bg-primary',
            'Dihapus' => 'bg-danger',
            default => 'bg-secondary',
        };
    }
}
