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
        Schema::create('sales', function (Blueprint $table) {
        $table->id();
        $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
        $table->string('customer_name')->nullable(); // للعميل اليدوي
        $table->decimal('total', 10, 2);
        $table->decimal('profit', 10, 2)->default(0);
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
