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
Schema::create('repair_spare_part', function (Blueprint $table) {
    $table->id();
    $table->foreignId('repair_id')->constrained('repairs')->onDelete('cascade');
    $table->foreignId('spare_part_id')->constrained('products')->onDelete('cascade');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_spare_part');
    }
};
