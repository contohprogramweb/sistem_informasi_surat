<?php

namespace App\Observers;

use App\Models\AuditLog;

class SuratMasukObserver
{
    /**
     * Handle the SuratMasuk "created" event.
     */
    public function created($model)
    {
        AuditLog::log('created', $model, [], $model->toArray());
    }

    /**
     * Handle the SuratMasuk "updated" event.
     */
    public function updated($model)
    {
        // Hanya log jika ada perubahan nyata
        $changes = $model->getChanges();
        if (!empty($changes)) {
            $original = $model->getOriginal();
            // Hapus timestamp dari perbandingan
            unset($original['updated_at'], $changes['updated_at']);
            
            if (!empty($changes)) {
                AuditLog::log('updated', $model, $original, $model->fresh()->toArray());
            }
        }
    }

    /**
     * Handle the SuratMasuk "deleted" event.
     */
    public function deleted($model)
    {
        AuditLog::log('deleted', $model, $model->toArray(), []);
    }
}
