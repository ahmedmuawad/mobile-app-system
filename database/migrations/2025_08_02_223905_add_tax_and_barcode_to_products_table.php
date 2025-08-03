<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_tax_included')->default(false)->after('sale_price');
            $table->decimal('tax_percentage', 5, 2)->nullable()->after('is_tax_included');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_tax_included', 'tax_percentage']);
            $table->dropForeign(['brand_id']);
        });
    }
};
