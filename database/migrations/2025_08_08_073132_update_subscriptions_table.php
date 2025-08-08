<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSubscriptionsTable extends Migration
{
    public function up()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('subscriptions','auto_renew')) {
                $table->boolean('auto_renew')->default(false)->after('status');
            }
            if (!Schema::hasColumn('subscriptions','provider')) {
                $table->string('provider',64)->nullable()->after('auto_renew');
            }
            if (!Schema::hasColumn('subscriptions','provider_subscription_id')) {
                $table->string('provider_subscription_id')->nullable()->after('provider');
            }
            if (!Schema::hasColumn('subscriptions','canceled_at')) {
                $table->timestamp('canceled_at')->nullable()->after('provider_subscription_id');
            }

            // Indexes
            $table->index('ends_at', 'idx_subscriptions_ends_at');
            $table->index(['company_id','status'], 'idx_subscriptions_company_status');
        });
    }

    public function down()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('subscriptions','canceled_at')) {
                $table->dropColumn('canceled_at');
            }
            if (Schema::hasColumn('subscriptions','provider_subscription_id')) {
                $table->dropColumn('provider_subscription_id');
            }
            if (Schema::hasColumn('subscriptions','provider')) {
                $table->dropColumn('provider');
            }
            if (Schema::hasColumn('subscriptions','auto_renew')) {
                $table->dropColumn('auto_renew');
            }

            // Drop indexes (guard with try/catch in case they don't exist)
            try { $table->dropIndex('idx_subscriptions_ends_at'); } catch (\Exception $e) {}
            try { $table->dropIndex('idx_subscriptions_company_status'); } catch (\Exception $e) {}
        });
    }
}
