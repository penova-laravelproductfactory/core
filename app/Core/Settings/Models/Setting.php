<?php

namespace App\Core\Settings\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Core\Settings — one key-value row, optionally grouped.
 *
 * Read/write through SettingsManager (cached), not this model directly.
 */
class Setting extends Model
{
    protected $fillable = ['group', 'key', 'value'];

    protected function casts(): array
    {
        // JSON cast so booleans, numbers and arrays round-trip cleanly.
        return ['value' => 'json'];
    }
}
