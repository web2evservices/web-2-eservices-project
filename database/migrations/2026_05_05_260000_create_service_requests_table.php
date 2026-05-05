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
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('citizen_id')->
            constrained('users')->
            onDelete('cascade');
            $table->foreignId('service_id')->
            constrained('services')->
            onDelete('cascade');
            $table->enum('status', ['Pending','In Review','Missing Documents','Approved','Rejected','Completed']);
            $table->string('qr_code')->unique();
            $table->foreignId('appointment_id')->nullable()->
            constrained('appointments')->
            onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
