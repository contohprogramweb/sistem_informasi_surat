<?php

namespace App\Observers;

use App\Models\AuditLog;

class DelegasiObserver
{
    public function created($model)
    {
        AuditLog::log('created', $model, [], $model->toArray());
    }

    public function updated($model)
    {
        $changes = $model->getChanges();
        if (!empty($changes)) {
            $original = $model->getOriginal();
            unset($original['updated_at'], $changes['updated_at']);
            
            if (!empty($changes)) {
                AuditLog::log('updated', $model, $original, $model->fresh()->toArray());
            }
        }
    }

    public function deleted($model)
    {
        AuditLog::log('deleted', $model, $model->toArray(), []);
    }
}
