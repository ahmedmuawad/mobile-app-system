<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       Setting::create([
            'store_name' => 'متجري الإلكتروني',
            'logo' => null,
            'address' => 'القاهرة، مصر',
            'phone' => '01000000000',
            'invoice_footer' => 'شكرًا لتسوقك معنا!',
        ]);

    }
}
