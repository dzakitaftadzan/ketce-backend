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
        Schema::table('orders', function (Blueprint $table) {
            // Menambahkan kolom user_id setelah kolom id
            $table->unsignedBigInteger('user_id')->after('id');
            
            // Opsional: Jika ingin membuat relasi foreign key ke tabel users
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Menghapus kolom user_id jika migrasi dibatalkan
            $table->dropColumn('user_id');
        });
    }
};