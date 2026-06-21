<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Carbon\Carbon;

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
        'tanggal_arsip',
        'tanggal_jatuh_aktif',
        'tanggal_jatuh_inaktif',
        'status_arsip',
        'alasan_hapus',
        'dimusnahkan_at',
        'dimusnahkan_by',
    ];

    protected $casts = [
        'tanggal_terima' => 'date',
        'tanggal_surat' => 'date',
        'indeks' => 'array',
        'tidak_perlu_disposisi' => 'boolean',
        'deleted_until' => 'datetime',
        'tanggal_arsip' => 'date',
        'tanggal_jatuh_aktif' => 'date',
        'tanggal_jatuh_inaktif' => 'date',
        'dimusnahkan_at' => 'datetime',
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

    /**
     * Check if all disposisi are completed
     */
    public function allDisposisiCompleted(): bool
    {
        if ($this->tidak_perlu_disposisi) {
            return true;
        }

        $totalDisposisi = $this->disposisi()->count();
        if ($totalDisposisi === 0) {
            return false;
        }

        $completedDisposisi = $this->disposisi()->where('status', 'Selesai')->count();
        return $totalDisposisi === $completedDisposisi;
    }

    /**
     * Check if surat can be archived
     */
    public function canArchive(): bool
    {
        if ($this->status_arsip !== 'aktif') {
            return false;
        }

        return $this->allDisposisiCompleted();
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
     * Archive the surat
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
