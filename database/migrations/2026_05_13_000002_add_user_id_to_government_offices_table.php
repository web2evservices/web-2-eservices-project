<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('government_offices', function (Blueprint $table) {
            if (!Schema::hasColumn('government_offices', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->after('municipality_id');
            }
        });

        DB::table('government_offices as g')
            ->join('offices as o', function ($join) {
                $join->on('g.name', '=', 'o.name')
                     ->on('g.address', '=', 'o.address')
                     ->on('g.municipality_id', '=', 'o.municipality_id');
            })
            ->whereNull('g.user_id')
            ->update(['g.user_id' => DB::raw('o.user_id')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('government_offices', function (Blueprint $table) {
            if (Schema::hasColumn('government_offices', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });
    }
};
