<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDurationDaysToPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds duration_days (int) to packages table (default 30 days).
     */
    public function up()
    {
        Schema::table('packages', function (Blueprint $table) {
            if (!Schema::hasColumn('packages', 'duration_days')) {
                $table->integer('duration_days')->unsigned()->default(30)->after('price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('packages', function (Blueprint $table) {
            if (Schema::hasColumn('packages', 'duration_days')) {
                $table->dropColumn('duration_days');
            }
        });
    }
}
