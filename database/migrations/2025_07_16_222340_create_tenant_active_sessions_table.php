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
        Schema::create('tenant_active_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('router_id');
            $table->unsignedBigInteger('user_id'); // FK to network_users
            $table->string('session_id');
            $table->string('ip_address');
            $table->string('mac_address');
            $table->bigInteger('bytes_in')->default(0);
            $table->bigInteger('bytes_out')->default(0);
            $table->timestamp('connected_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->enum('status', ['active', 'disconnected', 'expired'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_active_sessions');
    }
};
