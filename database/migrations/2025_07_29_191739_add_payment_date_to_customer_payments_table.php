<?php
// database/migrations/2025_07_29_999999_add_payment_date_to_customer_payments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('customer_payments', function (Blueprint $table) {
            // $table->date('payment_date')->nullable()->after('amount');
        });
    }

    public function down(): void {
        Schema::table('customer_payments', function (Blueprint $table) {
            $table->dropColumn('payment_date');
        });
    }
};
