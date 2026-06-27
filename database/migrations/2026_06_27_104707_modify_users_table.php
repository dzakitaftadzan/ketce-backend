<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('customer');
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'phone', 'is_active', 'login_attempts', 'locked_until']);
        });
    }
};