<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Modules\Booking — the bookings table.
 *
 * status is a plain string on purpose (default 'pending'); the domain
 * layer maps it to the BookingStatus enum (see Models/BookingStatus.php)
 * so adding a status never requires a schema change.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            // Indexed: the dashboard widget counts today's bookings.
            $table->dateTime('starts_at')->index();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
