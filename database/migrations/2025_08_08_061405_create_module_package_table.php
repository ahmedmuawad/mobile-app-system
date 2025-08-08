<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModulePackageTable extends Migration
{
    public function up()
    {
        Schema::create('module_package', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('packages')->onDelete('cascade');
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['package_id', 'module_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('module_package');
    }
}
