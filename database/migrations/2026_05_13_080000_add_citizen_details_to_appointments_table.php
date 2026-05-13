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
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('citizen_name')->after('service_id');
            $table->string('citizen_email')->after('citizen_name');
            $table->string('citizen_phone')->nullable()->after('citizen_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['citizen_name', 'citizen_email', 'citizen_phone']);
        });
    }
};
