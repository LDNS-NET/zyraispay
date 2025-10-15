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
        Schema::create('network_users', function (Blueprint $table) {
            $table->id(); // Primary key for network_users
            $table->string('account_number', 10)->unique(); // System-wide unique account number, max 8 digits
            $table->string('full_name')->nullable();
            $table->string('username')->unique();
            $table->string('password')->required();
            $table->string('phone')->nullable()->unique();
            $table->string('email')->nullable()->unique();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->string('location')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['hotspot', 'pppoe', 'static']);
            $table->timestamp('registered_at');
            $table->timestamp('expires_at')->nullable();
            $table->boolean('online')->default(false);

            $table->timestamps();
            // This table is for tenant DB only, no tenant_id needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('network_users');
    }
};
