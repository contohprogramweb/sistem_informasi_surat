<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SifatSurat extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sifat_surats';

    protected $fillable = ['nama', 'keterangan'];

    public function scopeSearch($query, $term)
    {
        return $query->where('nama', 'like', "%{$term}%");
    }
}
