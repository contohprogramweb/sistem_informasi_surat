<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportBatch extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'filename',
        'total_rows',
        'success_count',
        'failed_count',
        'errors',
        'status',
    ];

    protected $casts = [
        'errors' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getSuccessRateAttribute(): float
    {
        if ($this->total_rows === 0) {
            return 0;
        }
        return round(($this->success_count / $this->total_rows) * 100, 2);
    }

    public function getStatusBadgeAttribute(): string
    {
        $classes = [
            'processing' => 'bg-warning text-dark',
            'completed' => 'bg-success',
            'failed' => 'bg-danger',
        ];
        
        $label = ucfirst($this->status);
        $class = $classes[$this->status] ?? 'bg-secondary';
        
        return "<span class=\"badge {$class}\">{$label}</span>";
    }
}
