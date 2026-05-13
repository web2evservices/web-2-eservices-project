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
        Schema::table('request_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('request_histories', 'changed_by')) {
                $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete()->after('new_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_histories', function (Blueprint $table) {
            if (Schema::hasColumn('request_histories', 'changed_by')) {
                $table->dropConstrainedForeignId('changed_by');
            }
        });
    }
};
