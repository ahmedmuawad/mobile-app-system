<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use App\Models\Company;
use Carbon\Carbon;
use DB;

class ExpireSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire';
    protected $description = 'Mark subscriptions expired when end date passed and sync companies';

    public function handle()
    {
        $now = Carbon::now();
        $this->info("Checking subscriptions to expire at {$now}");

        // جلب الاشتراكات التي انتهت ولم تُعلم كـ expired بعد
        $subs = Subscription::where('status', 'active')
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', $now)
            ->get();

        if ($subs->isEmpty()) {
            $this->info('No expired subscriptions found.');
            return 0;
        }

        foreach ($subs as $sub) {
            DB::beginTransaction();
            try {
                $sub->status = 'expired';
                $sub->save();

                // مزامنة بيانات الشركة: ازالة package_id أو تخصيص باقة مجانية حسب سياستك
                $company = $sub->company;
                if ($company) {
                    // الخيار 1: نضع package_id = null عند انتهاء الاشتراك
                    $company->package_id = null;
                    $company->subscription_ends_at = null;

                    // إذا تريد تعطيل الشركة عند انتهاء الاشتراك: الغي التعليق السطرين التاليين
                    // $company->is_active = 0;

                    $company->save();
                }

                DB::commit();
                $this->info("Expired subscription #{$sub->id} for company {$sub->company_id}");
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Failed to expire subscription #{$sub->id}: {$e->getMessage()}");
            }
        }

        $this->info('Done.');
        return 0;
    }
}
