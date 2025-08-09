<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockAlertsTable extends Migration
{
    public function up()
    {
        Schema::create('stock_alerts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->decimal('threshold', 15, 4)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_notified_at')->nullable();
            $table->timestamps();

            $table->unique(['branch_id', 'product_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_alerts');
    }
}
