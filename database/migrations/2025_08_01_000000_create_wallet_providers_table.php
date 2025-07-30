<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('wallet_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('daily_limit', 12, 2)->default(60000);
            $table->decimal('monthly_limit', 12, 2)->default(200000);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('wallet_providers');
    }
};