<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'entity_type',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log an action
     */
    public static function log(string $action, $entity, array $oldValues = [], array $newValues = [])
    {
        $entityType = is_object($entity) ? get_class($entity) : $entity;
        $entityId = is_object($entity) && method_exists($entity, 'getKey') ? $entity->getKey() : null;

        return static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Get diff description
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
}
