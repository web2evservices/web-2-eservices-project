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
        Schema::create('government_offices', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->longText("address");
            $table->decimal('latitude', 10, 7);   
            $table->decimal('longitude', 10, 7);  
            $table->string('contact_info')->nullable();
            $table->foreignId("municipality_id")
            ->constrained("municipalities")
            ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('government__offices');
    }
};
