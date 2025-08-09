<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branch_product', function (Blueprint $table) {
            $table->unsignedInteger('low_stock_threshold')
                  ->default(0)
                  ->after('stock')
                  ->comment('الكمية التي يبدأ عندها التنبيه للمخزون المنخفض');
        });
    }

    public function down(): void
    {
        Schema::table('branch_product', function (Blueprint $table) {
            $table->dropColumn('low_stock_threshold');
        });
    }
};
