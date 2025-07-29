<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('repairs', function (Blueprint $table) {
            $table->enum('delivery_status', ['pending', 'received'])->default('pending')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('repairs', function (Blueprint $table) {
            $table->dropColumn('delivery_status');
        });
    }
};
