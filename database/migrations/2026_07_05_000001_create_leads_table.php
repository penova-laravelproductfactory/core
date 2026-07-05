<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Modules\Crm — the leads table.
 *
 * status is a free string ('new', 'contacted', 'qualified', …) — the
 * CRM module is deliberately light; a status enum can land later
 * without a schema change.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('status')->default('new');
            // Indexed: the dashboard widget counts today's leads on it.
            $table->timestamp('created_at')->index();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
