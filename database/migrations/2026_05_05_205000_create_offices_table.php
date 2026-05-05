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
        Schema::create('offices', function (Blueprint $table) {
             $table->id();
             $table->foreignId('municipality_id')->constrained()->cascadeOnDelete();
             $table->string('name');
             $table->string('email');
             $table->string('phone')->nullable();
             $table->string('address');
             $table->boolean('is_active')->default(true);
             $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offices');
    }
};
