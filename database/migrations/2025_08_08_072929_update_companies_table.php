<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCompaniesTable extends Migration
{
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies','billing_email')) {
                $table->string('billing_email')->nullable()->after('name');
            }
            if (!Schema::hasColumn('companies','timezone')) {
                $table->string('timezone',64)->default('Africa/Cairo')->after('billing_email');
            }
            if (!Schema::hasColumn('companies','settings')) {
                $table->json('settings')->nullable()->after('timezone');
            }
            if (!Schema::hasColumn('companies','subscription_ends_at')) {
                $table->timestamp('subscription_ends_at')->nullable()->after('package_id');
            }
        });
    }

    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies','subscription_ends_at')) {
                $table->dropColumn('subscription_ends_at');
            }
            if (Schema::hasColumn('companies','settings')) {
                $table->dropColumn('settings');
            }
            if (Schema::hasColumn('companies','timezone')) {
                $table->dropColumn('timezone');
            }
            if (Schema::hasColumn('companies','billing_email')) {
                $table->dropColumn('billing_email');
            }
        });
    }
}
