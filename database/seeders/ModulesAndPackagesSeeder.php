<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;
use App\Models\Package;
use Illuminate\Support\Facades\DB;

class ModulesAndPackagesSeeder extends Seeder
{
    public function run()
    {
        // أنشئ الموديولز
        $modules = [
            ['name' => 'المبيعات', 'slug' => 'sales', 'description' => 'نظام المبيعات'],
            ['name' => 'المخزون', 'slug' => 'inventory', 'description' => 'ادارة المخزون'],
            ['name' => 'المشتريات', 'slug' => 'purchases', 'description' => 'نظام المشتريات'],
            ['name' => 'الاصلاحات', 'slug' => 'repairs', 'description' => 'نظام الاصلاحات'],
            ['name' => 'المحفظة', 'slug' => 'wallets', 'description' => 'المدفوعات والمحافظ'],
            ['name' => 'التقارير', 'slug' => 'reports', 'description' => 'التقارير والاحصائيات'],
        ];

        foreach ($modules as $m) {
            Module::updateOrCreate(['slug' => $m['slug']], $m);
        }

        // أنشئ باقات تجريبية
        $basic = Package::updateOrCreate(['name' => 'Basic'], ['max_users' => 2, 'max_branches' => 1]);
        $standard = Package::updateOrCreate(['name' => 'Standard'], ['max_users' => 5, 'max_branches' => 3]);
        $pro = Package::updateOrCreate(['name' => 'Pro'], ['max_users' => 10, 'max_branches' => 10]);

        // اربط الموديولز بالباقات (مثال)
        $basic->modules()->sync(Module::whereIn('slug', ['sales', 'repairs'])->pluck('id')->toArray());
        $standard->modules()->sync(Module::whereIn('slug', ['sales', 'repairs', 'purchases', 'wallets'])->pluck('id')->toArray());
        $pro->modules()->sync(Module::pluck('id')->toArray());
    }
}
