<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('gateway')->nullable()->after('payment_method'); // stripe|tap|nowpayments
            $table->string('gateway_reference')->nullable()->after('gateway'); // charge_id, intent_id, invoice_id
            $table->string('payment_mode')->default('test')->after('gateway_reference'); // test|live
        });
    }
    public function down(): void {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['gateway', 'gateway_reference', 'payment_mode']);
        });
    }
};