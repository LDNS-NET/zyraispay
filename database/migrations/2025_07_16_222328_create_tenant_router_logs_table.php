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
        Schema::create('tenant_router_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('router_id');
            $table->string('action'); // connect, disconnect, sync, etc.
            $table->text('message');
            $table->enum('status', ['success', 'failed'])->default('success');
            $table->json('response_data')->nullable();
            $table->integer('execution_time')->nullable(); // in milliseconds
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_router_logs');
    }
};
