<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('address_id')->constrained();
            $table->string('order_code')->unique();
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('shipping_cost')->default(15000);
            $table->unsignedBigInteger('total');
            $table->string('payment_status')->default('pending');
            $table->string('order_status')->default('pending');
            $table->string('payment_proof')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};