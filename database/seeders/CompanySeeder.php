<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        // لو الشركة موجودة خلاص
        if (Company::where('subdomain', 'main')->exists()) {
            return;
        }

        $company = Company::create([
            'name' => 'الشركة الأم',
            'subdomain' => 'main',
            'email' => 'admin@main.com',
            'phone' => '01000000000',
            'address' => 'عنوان الشركة الأم',
            'locale' => 'ar',
            'is_active' => true,
        ]);

        $branch = Branch::create([
            'name' => 'الفرع الرئيسي',
            'address' => 'عنوان الفرع الرئيسي',
            'phone' => '0101111111',
            'is_main' => true,
            'is_active' => true,
            'company_id' => $company->id,
        ]);

        // ربط المستخدم الحالي بالشركة والفرع الجديد لو حبيت:
        $user = User::where('email', 'admin@admin.com')->first();

        if ($user) {
            $user->update([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
            ]);
        }
    }
}
