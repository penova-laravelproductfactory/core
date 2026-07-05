<?php

namespace App\Core\Logs\Services;

use App\Core\Logs\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Core\Logs — single entry point for writing audit entries.
 *
 * Direct use:
 *   app(ActivityLogger::class)->log('booking.confirmed', $booking);
 *
 * Automatic use: add the RecordsActivity trait
 * (App\Core\Support\Traits\RecordsActivity) to any model — Core or
 * Module — and created/updated/deleted are logged for you.
 */
class ActivityLogger
{
    public function log(string $action, ?Model $subject = null, array $changes = []): ?ActivityLog
    {
        if (! config('penova.logs.enabled')) {
            return null;
        }

        return ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'changes' => $changes,
        ]);
    }
}
