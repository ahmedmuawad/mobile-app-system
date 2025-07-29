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
    Schema::table('repairs', function (Blueprint $table) {
        $table->decimal('paid', 10, 2)->default(0);      // المدفوع
        $table->decimal('remaining', 10, 2)->default(0); // المتبقي
    });
}

public function down(): void
{
    Schema::table('repairs', function (Blueprint $table) {
        $table->dropColumn(['paid', 'remaining']);
    });
}

};
