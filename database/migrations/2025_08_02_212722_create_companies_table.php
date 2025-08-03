<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subdomain')->unique();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('logo')->nullable();
            $table->text('address')->nullable();
            $table->foreignId('package_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamp('subscription_ends_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->unsignedInteger('max_users')->default(1);
            $table->unsignedInteger('max_branches')->default(1);
            $table->string('locale')->default('ar');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
