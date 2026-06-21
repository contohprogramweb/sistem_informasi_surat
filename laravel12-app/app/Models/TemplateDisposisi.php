<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateDisposisi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'template_disposisi';

    protected $fillable = [
        'user_id', 'nama', 'instruksi_default',
        'tujuan_default', 'tembusan_default'
    ];

    protected $casts = [
        'tujuan_default' => 'array',
        'tembusan_default' => 'string',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where('nama', 'like', "%{$term}%");
    }
}
