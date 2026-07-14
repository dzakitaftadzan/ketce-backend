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
        Schema::table('addresses', function (Blueprint $table) {
            $table->string('recipient_name')->nullable()->after('user_id');
            $table->string('phone_number')->nullable()->after('recipient_name');
            $table->string('province')->nullable()->after('city');
            $table->string('district')->nullable()->after('province');
            $table->string('postal_code')->nullable()->after('district');
            $table->string('label')->nullable()->after('postal_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn([
                'recipient_name',
                'phone_number',
                'province',
                'district',
                'postal_code',
                'label',
            ]);
        });
    }
};
