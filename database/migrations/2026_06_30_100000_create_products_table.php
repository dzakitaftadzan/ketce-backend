<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id(); // ID unik otomatis
            $table->string('name'); // Kolom untuk nama produk
            $table->decimal('price', 10, 2); // Kolom harga (contoh: 10000.00)
            $table->integer('stock'); // Kolom jumlah stok
            $table->text('description')->nullable(); // Deskripsi (bisa kosong)
            $table->timestamps(); // Kolom created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};