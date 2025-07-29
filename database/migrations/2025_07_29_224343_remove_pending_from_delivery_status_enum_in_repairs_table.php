<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // إزالة "pending"
        DB::statement("ALTER TABLE repairs MODIFY COLUMN delivery_status ENUM('delivered', 'not_delivered', 'rejected') DEFAULT 'not_delivered'");
    }

    public function down(): void
    {
        // إعادة "pending" لو رجعت خطوة
        DB::statement("ALTER TABLE repairs MODIFY COLUMN delivery_status ENUM('delivered', 'not_delivered', 'rejected', 'pending') DEFAULT 'not_delivered'");
    }
};

