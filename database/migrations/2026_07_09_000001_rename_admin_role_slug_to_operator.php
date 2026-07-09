<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * RFC-002 / D-024 — rename the seeded role slug 'admin' → 'operator'.
 *
 * Renames in place, so every permission grant (role_permission) and user
 * assignment (role_user) is preserved — the role row keeps its id. Idempotent
 * and guarded: it acts only when an 'admin' slug exists and no 'operator' slug
 * already does, so fresh installs (seeded straight to 'operator') and re-runs
 * are safe.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('roles')->where('slug', 'admin')->exists()
            && ! DB::table('roles')->where('slug', 'operator')->exists()) {
            DB::table('roles')->where('slug', 'admin')->update(['slug' => 'operator']);
        }
    }

    public function down(): void
    {
        if (DB::table('roles')->where('slug', 'operator')->exists()
            && ! DB::table('roles')->where('slug', 'admin')->exists()) {
            DB::table('roles')->where('slug', 'operator')->update(['slug' => 'admin']);
        }
    }
};
