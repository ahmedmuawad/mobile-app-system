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
        Schema::create('customer_payments', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('repair_id')->nullable();
    $table->decimal('amount', 10, 2);
    $table->string('notes')->nullable();
    $table->timestamps();

    $table->foreign('repair_id')->references('id')->on('repairs')->onDelete('cascade');
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_payments');
    }
};
