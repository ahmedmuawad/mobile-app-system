<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE repairs MODIFY COLUMN delivery_status ENUM('delivered', 'not_delivered', 'rejected') DEFAULT 'not_delivered'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE repairs MODIFY COLUMN delivery_status ENUM('delivered', 'pending', 'rejected') DEFAULT 'pending'");
    }
};
