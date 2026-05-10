<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('government__offices', function (Blueprint $table) {
            $table->string('working_hours')->nullable()->after('contact_info');
        });
    }

    public function down(): void
    {
        Schema::table('government__offices', function (Blueprint $table) {
            $table->dropColumn('working_hours');
        });
    }
};