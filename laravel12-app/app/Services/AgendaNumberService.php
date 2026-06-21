<?php

namespace App\Services;

use App\Models\AgendaCounter;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AgendaNumberService
{
    /**
     * Generate nomor agenda otomatis dengan transaction dan SELECT FOR UPDATE
     * Format: [Urut 4 digit]/[Kode Unit]/[Tahun]
     */
    public function generateNext(string $unitCode): string
    {
        $year = date('Y');
        
        return DB::transaction(function () use ($unitCode, $year) {
            // Lock row for update to prevent race condition
            $counter = AgendaCounter::where('unit_code', $unitCode)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if (!$counter) {
                $counter = AgendaCounter::create([
                    'unit_code' => strtoupper($unitCode),
                    'year' => $year,
                    'last_number' => 0,
                ]);
            }

            // Increment counter
            $counter->increment('last_number');
            
            // Format: 0001/TU/2026
            $nextNumber = str_pad($counter->last_number, 4, '0', STR_PAD_LEFT);
            
            return "{$nextNumber}/{$counter->unit_code}/{$year}";
        });
    }

    /**
     * Get current counter value without incrementing
     */
    public function getCurrent(string $unitCode): ?string
    {
        $year = date('Y');
        
        $counter = AgendaCounter::where('unit_code', $unitCode)
            ->where('year', $year)
            ->first();

        if (!$counter || $counter->last_number === 0) {
            return null;
        }

        $nextNumber = str_pad($counter->last_number, 4, '0', STR_PAD_LEFT);
        return "{$nextNumber}/{$counter->unit_code}/{$year}";
    }

    /**
     * Get unit code from user's unit
     */
    public function getUnitCodeFromUserId(int $userId): string
    {
        $user = \App\Models\User::with('unit')->find($userId);
        
        if (!$user || !$user->unit) {
            throw new \Exception('User tidak memiliki unit yang valid');
        }

        return strtoupper($user->unit->kode_unit);
    }
}
