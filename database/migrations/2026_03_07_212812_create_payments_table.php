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
        Schema::create('payments', function (Blueprint $table) {
          $table->id();
          $table->foreignId('service_request_id')->
          constrained('service_requests')->
          onDelete('cascade');
          $table->decimal('amount', 10, 2);
          $table->string('currency', 10);
          $table->enum('payment_method', ['card','crypto']);
          $table->enum('status', ['Pending','Completed','Failed']);
          $table->string('transaction_id')->nullable();
          $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
