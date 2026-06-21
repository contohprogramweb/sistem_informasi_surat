<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\SuratKeluarStatus;

class SuratKeluar extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'unit_pembuat_id', 'klasifikasi_id', 'sifat_id',
        'tujuan', 'perihal', 'isi_ringkas',
        'nomor_surat_final', 'tanggal_surat_final',
        'status', 'catatan_review', 'alasan_tolak',
        'cara_kirim', 'tanggal_kirim', 'resi',
        'created_by', 'reviewer_id', 'approver_id', 'signed_by',
    ];

    protected $casts = [
        'tanggal_surat_final' => 'date',
        'tanggal_kirim' => 'date',
        'status' => SuratKeluarStatus::class,
    ];

    public function unitPembuat(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_pembuat_id');
    }

    public function klasifikasi(): BelongsTo
    {
        return $this->belongsTo(KlasifikasiArsip::class, 'klasifikasi_id');
    }

    public function sifat(): BelongsTo
    {
        return $this->belongsTo(SifatSurat::class, 'sifat_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function signedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(SuratKeluarHistory::class);
    }

    public function lampiran(): HasMany
    {
        return $this->hasMany(Lampiran::class, 'surat_keluar_id');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', SuratKeluarStatus::Draft);
    }

    public function scopeReview($query)
    {
        return $query->where('status', SuratKeluarStatus::Review);
    }

    public function scopeDisetujui($query)
    {
        return $query->where('status', SuratKeluarStatus::Disetujui);
    }

    public function scopeTerkirim($query)
    {
        return $query->where('status', SuratKeluarStatus::Terkirim);
    }

    public function canEdit(): bool
    {
        return $this->status === SuratKeluarStatus::Draft;
    }

    public function canDelete(): bool
    {
        return $this->status === SuratKeluarStatus::Draft || $this->status === SuratKeluarStatus::Ditolak;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return 'bg-' . $this->status->color();
    }
}
