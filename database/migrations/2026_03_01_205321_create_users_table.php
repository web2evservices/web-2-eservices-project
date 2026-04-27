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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string("username")->unique();
            $table->string("email")->unique();
            $table->longText("password");
            $table->enum('role', ['admin', 'office_user','citizen'])->default('citizen');
            $table->longText("tel");
            $table->enum("status",["active","inactive"])->default('active');
            $table->boolean("two_factor_enabled")->default('0');
            $table ->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
