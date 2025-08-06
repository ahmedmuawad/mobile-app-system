<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBranchIdToCustomerPaymentsTable extends Migration
{
    public function up()
    {
        Schema::table('customer_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->after('repair_id');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('customer_payments', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
    }
}
