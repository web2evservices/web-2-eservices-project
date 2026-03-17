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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->
            constrained('government__offices')->
            onDelete('cascade');
            $table->foreignId('citizen_id')->
            constrained('users')->
            onDelete('cascade');
            $table->foreignId('service_id')->
            constrained('services')->
            onDelete('cascade');
            $table->date('date');
            $table->time('time_slot');
            $table->enum('status', ['Scheduled','Completed','Cancelled']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
