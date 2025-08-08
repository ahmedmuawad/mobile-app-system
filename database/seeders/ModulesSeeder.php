<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ModulesSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        $modules = [
            ['name' => 'المبيعات', 'slug' => 'sales', 'feature_key' => 'sales', 'description' => 'نظام المبيعات والفواتير', 'is_active' => 1],
            ['name' => 'المنتجات', 'slug' => 'products', 'feature_key' => 'products', 'description' => 'إدارة المنتجات والمخزون', 'is_active' => 1],
            ['name' => 'الإصلاحات', 'slug' => 'repairs', 'feature_key' => 'repairs', 'description' => 'إدارة عمليات الصيانة والورش', 'is_active' => 1],
            ['name' => 'التقارير', 'slug' => 'reports', 'feature_key' => 'reports', 'description' => 'التقارير المالية وتقارير الأداء', 'is_active' => 1],
            ['name' => 'المحافظ', 'slug' => 'wallets', 'feature_key' => 'wallets', 'description' => 'إدارة المحافظ والمعاملات المالية', 'is_active' => 1],
        ];

        foreach ($modules as $module) {
            DB::table('modules')->updateOrInsert(
                ['slug' => $module['slug']],
                array_merge($module, ['created_at' => $now, 'updated_at' => $now])
            );
        }
    }
}
