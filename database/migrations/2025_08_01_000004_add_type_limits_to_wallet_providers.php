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
                Schema::table('wallet_providers', function (Blueprint $table) {
                    $table->decimal('daily_send_limit')->nullable();
                    $table->decimal('daily_receive_limit')->nullable();
                    $table->decimal('daily_bill_limit')->nullable();
                });
            }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_providers', function (Blueprint $table) {
            //
        });
    }
};
