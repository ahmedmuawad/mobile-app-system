<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('branch_product', function (Blueprint $table) {
            $table->decimal('purchase_price', 10, 2)->default(0)->after('price');
            $table->boolean('is_tax_included')->default(false)->after('stock');
            $table->decimal('tax_percentage', 5, 2)->nullable()->after('is_tax_included');
        });
    }

    public function down(): void
    {
        Schema::table('branch_product', function (Blueprint $table) {
            $table->dropColumn(['purchase_price', 'is_tax_included', 'tax_percentage']);
        });
    }
};
