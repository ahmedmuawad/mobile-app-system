<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::table('sales', function (Blueprint $table) {
        $table->unsignedBigInteger('company_id')->nullable()->after('id');
    });
}

public function down()
{
    Schema::table('sales', function (Blueprint $table) {
        $table->dropColumn(['company_id', 'branch_id']);
    });
}
};
