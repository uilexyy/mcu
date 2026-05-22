<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait LogActivity
{
    protected static function bootLogActivity()
    {
        static::created(function ($model) {
            static::log('created', $model, $model->toArray());
        });

        static::updated(function ($model) {
            $old = [];
            $new = [];
            foreach ($model->getDirty() as $key => $value) {
                $old[$key] = $model->getOriginal($key);
                $new[$key] = $value;
            }
            if (! empty($new)) {
                static::log('updated', $model, $new, $old);
            }
        });

        static::deleted(function ($model) {
            static::log('deleted', $model, null, $model->toArray());
        });
    }

    protected static function log(string $action, $model, ?array $newValues = null, ?array $oldValues = null): void
    {
        $user = Auth::user();

        ActivityLog::create([
            'user_id' => $user?->id,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => static::makeDescription($action, $model),
        ]);
    }

    protected static function makeDescription(string $action, $model): string
    {
        $modelName = class_basename($model);
        $label = match ($modelName) {
            'McuRegistration' => 'Pendaftaran MCU',
            'McuPhysicalExam' => 'Pemeriksaan Fisik',
            'McuLabResult' => 'Hasil Lab',
            'McuRadiologyResult' => 'Hasil Radiologi',
            'McuResult' => 'Hasil MCU',
            'McuPackage' => 'Paket MCU',
            'User' => 'User',
            default => $modelName,
        };

        $idLabel = method_exists($model, 'getActivityLabel')
            ? $model->getActivityLabel()
            : "#{$model->id}";

        return match ($action) {
            'created' => "{$label} {$idLabel} dibuat",
            'updated' => "{$label} {$idLabel} diperbarui",
            'deleted' => "{$label} {$idLabel} dihapus",
            default => "{$label} {$idLabel} {$action}",
        };
    }
}
