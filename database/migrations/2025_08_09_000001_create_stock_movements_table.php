<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockMovementsTable extends Migration
{
    public function up()
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('spare_part_id')->nullable();
            $table->string('movement_type', 50); // purchase, sale, sale_return, purchase_return, repair_use
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('qty_before', 15, 4)->default(0);
            $table->decimal('qty_change', 15, 4)->default(0);
            $table->decimal('qty_after', 15, 4)->default(0);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('branch_id');
            $table->index('product_id');
            $table->index('spare_part_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_movements');
    }
}
