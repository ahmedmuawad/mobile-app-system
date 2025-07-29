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
        $table->decimal('discount', 10, 2)->default(0)->after('repair_cost');
    });
    }

    /**
     * Reverse the migrations.
     */
   public function down()
{
    Schema::table('repairs', function (Blueprint $table) {
    });
}
};
