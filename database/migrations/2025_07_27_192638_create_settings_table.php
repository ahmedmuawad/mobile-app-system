<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('store_name')->nullable();         // اسم المتجر
            $table->string('logo')->nullable();               // شعار المتجر (مسار الصورة)
            $table->string('address')->nullable();            // العنوان
            $table->string('phone')->nullable();              // رقم الهاتف
            $table->text('invoice_footer')->nullable();       // رسالة أسفل الفاتورة
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
