<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('base_price', 10, 2)->after('sale_price')->nullable();
            $table->decimal('tax_value', 10, 2)->after('base_price')->nullable();
            $table->decimal('tax_percentage', 5, 2)->after('tax_value')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn(['base_price', 'tax_value', 'tax_percentage']);
        });
    }
};
