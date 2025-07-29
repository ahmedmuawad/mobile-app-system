<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('customer_payments', function (Blueprint $table) {
            $table->date('payment_date')->after('amount')->nullable();
        });
    }

    public function down()
    {
        Schema::table('customer_payments', function (Blueprint $table) {
            $table->dropColumn('payment_date');
        });
    }
};
