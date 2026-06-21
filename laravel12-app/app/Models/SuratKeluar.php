<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Enums\SuratKeluarStatus;
use Carbon\Carbon;

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
        'hash_file_final', 'pdf_final_path', 'signed_at',
        'tanggal_arsip',
        'tanggal_jatuh_aktif',
        'tanggal_jatuh_inaktif',
        'status_arsip',
        'alasan_hapus',
        'dimusnahkan_at',
        'dimusnahkan_by',
    ];

    protected $casts = [
        'tanggal_surat_final' => 'date',
        'tanggal_kirim' => 'date',
        'signed_at' => 'datetime',
        'status' => SuratKeluarStatus::class,
        'tanggal_arsip' => 'date',
        'tanggal_jatuh_aktif' => 'date',
        'tanggal_jatuh_inaktif' => 'date',
        'dimusnahkan_at' => 'datetime',
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

    /**
     * Check if surat keluar can be archived (must be "Terkirim")
     */
    public function canArchive(): bool
    {
        if ($this->status_arsip !== 'aktif') {
            return false;
        }

        return $this->status === SuratKeluarStatus::Terkirim;
    }

    /**
     * Calculate retention dates based on classification
     */
    public function calculateRetentionDates(): void
    {
        if (!$this->klasifikasi) {
            return;
        }

        $startDate = $this->tanggal_arsip ?? now();

        // Calculate active retention end date
        if ($this->klasifikasi->retensi_aktif) {
            $this->tanggal_jatuh_aktif = $startDate->copy()->addYears($this->klasifikasi->retensi_aktif);
        }

        // Calculate inactive retention end date
        if ($this->klasifikasi->retensi_inaktif && $this->tanggal_jatuh_aktif) {
            $this->tanggal_jatuh_inaktif = $this->tanggal_jatuh_aktif->copy()->addYears($this->klasifikasi->retensi_inaktif);
        }

        $this->save();
    }

    /**
     * Archive the surat keluar
     */
    public function archive(): bool
    {
        if (!$this->canArchive()) {
            return false;
        }

        $this->status_arsip = 'aktif';
        $this->tanggal_arsip = now();
        $this->calculateRetentionDates();
        $this->save();

        return true;
    }

    /**
     * Mark surat as destroyed (pemusnahan)
     */
    public function markAsDestroyed(User $user): void
    {
        $this->status_arsip = 'dimusnahkan';
        $this->dimusnahkan_at = now();
        $this->dimusnahkan_by = $user->id;
        $this->save();
    }

    /**
     * Check if surat is read-only (archived or destroyed)
     */
    public function isReadOnly(): bool
    {
        return in_array($this->status_arsip, ['aktif', 'inaktif', 'dimusnahkan']);
    }

    /**
     * Soft delete with 30-day retention
     */
    public function softDeleteWithReason(string $reason, User $user): void
    {
        $this->alasan_hapus = $reason;
        $this->deleted_until = now()->addDays(30);
        $this->save();
    }

    /**
     * Restore from soft delete
     */
    public function restoreFromSoftDelete(): void
    {
        $this->alasan_hapus = null;
        $this->deleted_until = null;
        $this->save();
    }

    /**
     * Check if can be restored
     */
    public function canBeRestored(): bool
    {
        return $this->deleted_until && $this->deleted_until->isFuture();
    }

    /**
     * Get arsip status badge class
     */
    public function getArsipStatusBadgeClassAttribute(): string
    {
        return match($this->status_arsip) {
            'aktif' => 'bg-success',
            'inaktif' => 'bg-warning text-dark',
            'dimusnahkan' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    /**
     * Relationship to berita acara pemusnahan
     */
    public function beritaAcaraDetails(): MorphMany
    {
        return $this->morphMany(BeritaAcaraDetail::class, 'arsip');
    }

    /**
     * Relationship to arsip notifications
     */
    public function arsipNotifications(): MorphMany
    {
        return $this->morphMany(ArsipNotification::class, 'arsip');
    }
}
