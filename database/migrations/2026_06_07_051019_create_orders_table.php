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
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('address_id')->constrained('addresses');
            $table->string('order_code')->unique();
            $table->bigInteger('subtotal');
            $table->bigInteger('shipping_cost')->default(15000);
            $table->bigInteger('total');
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            $table->enum('order_status', ['pending', 'confirmed', 'packed', 'delivering', 'delivered', 'cancelled'])->default('pending');
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