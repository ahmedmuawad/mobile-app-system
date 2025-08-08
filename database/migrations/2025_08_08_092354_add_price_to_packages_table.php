<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPriceToPackagesTable extends Migration
{
    public function up()
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->default(0); // تضيف عمود price من نوع decimal
        });
    }

    public function down()
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
}
