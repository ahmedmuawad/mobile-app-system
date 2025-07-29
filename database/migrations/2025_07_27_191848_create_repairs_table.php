<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('repairs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_name')->nullable(); // للعميل اليدوي

            $table->string('device_type');
            $table->text('problem_description');

            $table->unsignedBigInteger('spare_part_id')->nullable(); // product_id

            $table->enum('status', ['جاري', 'تم الإصلاح', 'لم يتم الإصلاح'])->default('جاري');

            $table->decimal('repair_cost', 10, 2)->default(0); // المصنعية
            $table->decimal('total', 10, 2)->default(0); // إجمالي (قطعة + مصنعية)

            $table->timestamps();

            // علاقات
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->foreign('spare_part_id')->references('id')->on('products')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repairs');
    }
};
