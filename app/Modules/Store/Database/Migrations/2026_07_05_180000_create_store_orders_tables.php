<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Modules\Store — orders + order items (v0.1 guest checkout).
 *
 * order_items denormalizes product_name/price on purpose: an order is a
 * historical document — renaming or repricing a product later must not
 * rewrite what the customer actually bought. product_id stays as a soft
 * link (nullOnDelete) for navigation while it exists.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_orders', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique(); // customer-facing reference
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->text('shipping_address');
            $table->string('status')->default('pending')->index();
            $table->string('payment_status')->default('unpaid')->index();
            $table->decimal('total', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('store_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('store_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('store_products')->nullOnDelete();
            $table->string('product_name'); // snapshot at order time
            $table->decimal('price', 10, 2); // snapshot at order time
            $table->unsignedInteger('quantity');
            $table->decimal('subtotal', 10, 2); // price * quantity, computed server-side
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_order_items');
        Schema::dropIfExists('store_orders');
    }
};
