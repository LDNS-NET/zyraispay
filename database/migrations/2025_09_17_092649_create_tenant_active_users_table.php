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
        Schema::create('tenant_active_users', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->enum('user_type', ['hotspot', 'pppoe', 'static']);
            $table->string('ip/mac_address');
            $table->timestamp('session_start')->nullable();
            $table->timestamp('session_end')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('mikrotik_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_active_users');
    }
};
