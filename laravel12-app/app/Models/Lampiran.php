<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Lampiran extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'attachable_type',
        'attachable_id',
        'filename',
        'original_name',
        'hash',
        'mime_type',
        'file_size',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get full path to the file
     */
    public function getFullPathAttribute(): string
    {
        return storage_path('app/' . $this->filename);
    }

    /**
     * Check if file exists
     */
    public function fileExists(): bool
    {
        return file_exists($this->full_path);
    }
}
