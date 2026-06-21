<?php

namespace App\Observers;

use App\Models\AuditLog;

class UserObserver
{
    public function created($model)
    {
        // Jangan log password
        $data = $model->toArray();
        unset($data['password']);
        AuditLog::log('created', $model, [], $data);
    }

    public function updated($model)
    {
        $changes = $model->getChanges();
        if (!empty($changes)) {
            $original = $model->getOriginal();
            unset($original['updated_at'], $changes['updated_at'], $original['password'], $changes['password']);
            
            if (!empty($changes)) {
                $newData = $model->fresh()->toArray();
                unset($newData['password']);
                AuditLog::log('updated', $model, $original, $newData);
            }
        }
    }

    public function deleted($model)
    {
        $data = $model->toArray();
        unset($data['password']);
        AuditLog::log('deleted', $model, $data, []);
    }
}
