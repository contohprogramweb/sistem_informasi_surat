<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BeritaAcaraPemusnahan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'berita_acara_pemusnahan';

    protected $fillable = [
        'nomor_berita_acara',
        'tanggal_berita_acara',
        'keterangan',
        'created_by',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'tanggal_berita_acara' => 'date',
        'approved_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(BeritaAcaraDetail::class, 'berita_acara_id');
    }

    /**
     * Generate unique nomor berita acara
     */
    public static function generateNomorBeritaAcara(): string
    {
        $year = now()->format('Y');
        $month = now()->format('m');
        $lastNumber = static::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count() + 1;

        return sprintf('BA/%s/%s/%04d', $year, $month, $lastNumber);
    }

    /**
     * Add arsip to berita acara
     */
    public function addArsip($arsip): BeritaAcaraDetail
    {
        return $this->details()->create([
            'arsip_id' => $arsip->id,
            'arsip_type' => get_class($arsip),
            'nomor_surat' => $arsip->nomor_surat,
            'tanggal_surat' => $arsip->tanggal_surat ?? $arsip->tanggal_kirim,
            'perihal' => $arsip->perihal,
            'retensi_aktif_tahun' => $arsip->klasifikasi?->retensi_aktif ?? 0,
            'retensi_inaktif_tahun' => $arsip->klasifikasi?->retensi_inaktif ?? 0,
            'tanggal_jatuh_tempo' => $arsip->tanggal_jatuh_inaktif ?? $arsip->tanggal_jatuh_aktif,
        ]);
    }

    /**
     * Get total arsip in this berita acara
     */
    public function getTotalArsipAttribute(): int
    {
        return $this->details()->count();
    }

    /**
     * Scope for approved berita acara
     */
    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }
}
