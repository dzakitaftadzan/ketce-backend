<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Menambahkan kolom yang kurang satu per satu
            if (!Schema::hasColumn('orders', 'user_id')) $table->unsignedBigInteger('user_id');
            if (!Schema::hasColumn('orders', 'address_id')) $table->unsignedBigInteger('address_id');
            if (!Schema::hasColumn('orders', 'order_code')) $table->string('order_code');
            if (!Schema::hasColumn('orders', 'subtotal')) $table->decimal('subtotal', 15, 2);
            if (!Schema::hasColumn('orders', 'shipping_cost')) $table->decimal('shipping_cost', 15, 2);
            if (!Schema::hasColumn('orders', 'total_price')) $table->decimal('total_price', 15, 2);
            if (!Schema::hasColumn('orders', 'payment_status')) $table->string('payment_status');
            if (!Schema::hasColumn('orders', 'order_status')) $table->string('order_status');
            if (!Schema::hasColumn('orders', 'payment_proof')) $table->string('payment_proof')->nullable();
        });
    }

    public function down(): void
    {
        // Tidak perlu diisi untuk rollback, cukup biarkan kosong
    }
};