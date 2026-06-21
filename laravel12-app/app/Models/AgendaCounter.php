<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgendaCounter extends Model
{
    protected $fillable = [
        'unit_code',
        'year',
        'last_number',
    ];

    protected $casts = [
        'year' => 'integer',
        'last_number' => 'integer',
    ];

    public function scopeForUnitAndYear($query, $unitCode, $year)
    {
        return $query->where('unit_code', $unitCode)->where('year', $year);
    }
}
