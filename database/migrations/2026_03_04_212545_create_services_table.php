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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->
            constrained('government__offices')->
            onDelete('cascade');
            $table->string('name');
            $table->foreignId('category_id')->
            constrained('service__categories')->
            onDelete('cascade');
            $table->decimal('price', 10, 2);
            $table->integer('duration'); 
            $table->json('required_documents')->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
