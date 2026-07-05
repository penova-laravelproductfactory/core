<?php

namespace App\Core\Support\Traits;

use App\Core\Logs\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;

/**
 * Core\Support — drop-in audit logging for any Eloquent model.
 *
 * Add `use RecordsActivity;` to a model (Core or Module) and its
 * created / updated / deleted events are written to the activity log
 * as "<table>.created" etc. This is the Core abstraction Modules hook
 * into instead of rolling their own logging.
 */
trait RecordsActivity
{
    public static function bootRecordsActivity(): void
    {
        foreach (['created', 'updated', 'deleted'] as $event) {
            static::$event(function (Model $model) use ($event) {
                app(ActivityLogger::class)->log(
                    "{$model->getTable()}.{$event}",
                    $model,
                    $event === 'updated' ? $model->getChanges() : [],
                );
            });
        }
    }
}
