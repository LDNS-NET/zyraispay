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
        Schema::create('tenant_mikrotiks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ip_address')->nullable();
            $table->integer('api_port')->default(8728);
            $table->integer('ssh_port')->default(22);
            $table->unsignedBigInteger('openvpn_profile_id')->nullable();
            $table->string('router_username');
            $table->text('router_password'); // encrypted
            $table->enum('connection_type', ['api', 'ssh', 'ovpn'])->default('api');
            $table->timestamp('last_seen_at')->nullable();
            $table->enum('status', ['pending', 'online', 'offline'])->default('pending');
            $table->string('model')->nullable(); // RB750, RB450G, etc.
            $table->string('os_version')->nullable();
            $table->bigInteger('uptime')->nullable(); // in seconds
            $table->decimal('cpu_usage', 5, 2)->nullable(); // percentage
            $table->decimal('memory_usage', 5, 2)->nullable(); // percentage
            $table->decimal('temperature', 5, 2)->nullable(); // in celsius
            $table->text('notes')->nullable();
            $table->string('sync_token', 64)->nullable(); // for secure sync endpoint
            $table->unsignedBigInteger('created_by')->nullable(); // for tenant/user scoping
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_mikrotiks');
    }
};
