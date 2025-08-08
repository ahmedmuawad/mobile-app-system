<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateModulesTable extends Migration
{
    public function up()
    {
        Schema::table('modules', function (Blueprint $table) {
            if (!Schema::hasColumn('modules', 'icon')) {
                $table->string('icon')->nullable()->after('description');
            }
            if (!Schema::hasColumn('modules', 'feature_key')) {
                $table->string('feature_key', 128)->nullable()->unique()->after('icon');
            }
            if (!Schema::hasColumn('modules', 'settings')) {
                $table->json('settings')->nullable()->after('feature_key');
            }
            if (!Schema::hasColumn('modules', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('settings');
            }
        });
    }

    public function down()
    {
        Schema::table('modules', function (Blueprint $table) {
            if (Schema::hasColumn('modules', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
            if (Schema::hasColumn('modules', 'settings')) {
                $table->dropColumn('settings');
            }
            if (Schema::hasColumn('modules', 'feature_key')) {
                $table->dropUnique(['feature_key']);
                $table->dropColumn('feature_key');
            }
            if (Schema::hasColumn('modules', 'icon')) {
                $table->dropColumn('icon');
            }
        });
    }
}
