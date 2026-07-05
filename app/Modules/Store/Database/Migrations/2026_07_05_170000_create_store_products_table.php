<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Modules\Store — the store_products table.
 *
 * type is a plain string backed by the ProductType enum (physical /
 * virtual / downloadable) — same pattern as bookings.status. stock and
 * download_url are nullable because they only apply to some types.
 * Classic minimal e-commerce product shape; categories, attributes and
 * pricing rules bolt on later without touching these columns.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->default('physical');
            $table->decimal('price', 10, 2);
            $table->string('sku')->nullable();
            $table->integer('stock')->nullable(); // physical products only
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->string('download_url')->nullable(); // downloadable products only
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_products');
    }
};
