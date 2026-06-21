<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BeritaAcaraDetail extends Model
{
    protected $table = 'berita_acara_detail';

    protected $fillable = [
        'berita_acara_id',
        'arsip_id',
        'arsip_type',
        'nomor_surat',
        'tanggal_surat',
        'perihal',
        'retensi_aktif_tahun',
        'retensi_inaktif_tahun',
        'tanggal_jatuh_tempo',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
        'tanggal_jatuh_tempo' => 'date',
        'retensi_aktif_tahun' => 'integer',
        'retensi_inaktif_tahun' => 'integer',
    ];

    public function beritaAcara(): BelongsTo
    {
        return $this->belongsTo(BeritaAcaraPemusnahan::class, 'berita_acara_id');
    }

    public function arsip(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the original arsip model (even if soft deleted)
     */
    public function getOriginalArsipAttribute()
    {
        return $this->arsip_type::withTrashed()->find($this->arsip_id);
    }
}
