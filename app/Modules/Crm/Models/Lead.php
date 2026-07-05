<?php

namespace App\Modules\Crm\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modules\Crm — a sales lead. Kept minimal on purpose: the CRM module
 * mainly stress-tests the module/widget architecture.
 */
class Lead extends Model
{
    protected $table = 'leads';

    protected $fillable = ['name', 'email', 'status'];

    // Mirrors the DB default so a freshly instantiated (not yet
    // refreshed) model reports the same status the row will get.
    protected $attributes = ['status' => 'new'];
}
