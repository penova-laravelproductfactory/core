<?php

namespace App\Core\Logs\Models;

use App\Core\Users\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Core\Logs — one audit entry: who did what to which record.
 */
class ActivityLog extends Model
{
    // Immutable audit rows: created once, never updated.
    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'action', 'subject_type', 'subject_id', 'changes'];

    protected function casts(): array
    {
        return ['changes' => 'json'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** The model the action was performed on. */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
