<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'entity', // Changed from entity_type to match migration
        'entity_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Relasi ke User yang melakukan aksi
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope untuk filter berdasarkan entity
     */
    public function scopeForEntity($query, string $entityClass)
    {
        return $query->where('entity', $entityClass);
    }

    /**
     * Scope untuk filter berdasarkan rentang tanggal
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Log an action (Static helper)
     */
    public static function log(string $action, $entity, array $oldValues = [], array $newValues = [])
    {
        $entityType = is_object($entity) ? get_class($entity) : $entity;
        $entityId = is_object($entity) && method_exists($entity, 'getKey') ? $entity->getKey() : null;

        return static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'entity' => $entityType, // Match migration field name
            'entity_id' => $entityId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Format diff untuk ditampilkan di view
     * Mengembalikan array perubahan field
     */
    public function getDiffAttribute(): array
    {
        $diff = [];
        
        if ($this->action === 'deleted') {
            return ['removed' => $this->old_values ?? []];
        }

        if ($this->action === 'created') {
            return ['added' => $this->new_values ?? []];
        }

        // Updated: bandingkan old vs new
        $old = $this->old_values ?? [];
        $new = $this->new_values ?? [];

        foreach ($new as $key => $value) {
            if (!isset($old[$key]) || $old[$key] !== $value) {
                $diff[$key] = [
                    'old' => $old[$key] ?? null,
                    'new' => $value,
                ];
            }
        }

        // Cek field yang dihapus (jarang terjadi tapi mungkin)
        foreach ($old as $key => $value) {
            if (!isset($new[$key])) {
                $diff[$key] = [
                    'old' => $value,
                    'new' => null,
                ];
            }
        }

        return $diff;
    }

    /**
     * Get diff description (legacy support)
     */
    public function getDiffDescriptionAttribute(): string
    {
        $diffs = [];
        
        if ($this->old_values && $this->new_values) {
            foreach ($this->new_values as $key => $newValue) {
                $oldValue = $this->old_values[$key] ?? null;
                if ($oldValue !== $newValue) {
                    $diffs[] = "{$key}: " . (is_null($oldValue) ? 'null' : $oldValue) . " → " . (is_null($newValue) ? 'null' : $newValue);
                }
            }
        }

        return implode('; ', $diffs);
    }

    /**
     * Deskripsi human-readable untuk aksi
     */
    public function getDescriptionAttribute(): string
    {
        $modelName = class_basename($this->entity);
        $actionMap = [
            'created' => 'Membuat',
            'updated' => 'Memperbarui',
            'deleted' => 'Menghapus',
        ];

        $actionText = $actionMap[$this->action] ?? ucfirst($this->action);
        
        return "{$actionText} {$modelName} #{$this->entity_id}";
    }

    /**
     * Hapus log lama secara otomatis (dipanggil oleh Scheduler)
     * Retention 5 tahun
     */
    public static function pruneOldLogs()
    {
        $cutoffDate = now()->subYears(5);
        
        return self::where('created_at', '<', $cutoffDate)->delete();
    }
}
