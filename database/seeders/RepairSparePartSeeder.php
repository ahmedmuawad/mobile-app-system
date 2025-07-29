<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Repair;
use App\Models\SparePart;

class RepairSparePartSeeder extends Seeder
{
    public function run()
    {
        // نفترض في قاعدة البيانات عندنا 3 قطع غيار جاهزين
        $sparePart1 = SparePart::firstOrCreate(['name' => 'بطارية', 'category_id' => 1, 'sale_price' => 150]);
        $sparePart2 = SparePart::firstOrCreate(['name' => 'شاشة', 'category_id' => 1, 'sale_price' => 400]);
        $sparePart3 = SparePart::firstOrCreate(['name' => 'زرار صوت', 'category_id' => 1, 'sale_price' => 50]);

        // إنشاء فاتورة صيانة
        $repair = Repair::create([
            'customer_name' => 'عميل تجريبي',
            'device_type' => 'iPhone X',
            'status' => 'جاري',
            'problem_description' => 'شاشة مكسورة',
            'repair_type' => 'hardware',
            'repair_cost' => 100,
            'discount' => 10,
        ]);

        // ربط قطع الغيار مع الفاتورة مع تحديد الكميات
        $repair->spareParts()->attach([
            $sparePart1->id => ['quantity' => 2],
            $sparePart2->id => ['quantity' => 1],
            $sparePart3->id => ['quantity' => 3],
        ]);
    }
}
