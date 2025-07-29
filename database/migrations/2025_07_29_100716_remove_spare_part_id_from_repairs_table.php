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
                $table->dropForeign(['spare_part_id']);
                $table->dropColumn('spare_part_id');
            });
        }

        public function down(): void
        {
            Schema::table('repairs', function (Blueprint $table) {
                $table->unsignedBigInteger('spare_part_id')->nullable();
                $table->foreign('spare_part_id')->references('id')->on('products')->onDelete('set null');
            });
        }

};
