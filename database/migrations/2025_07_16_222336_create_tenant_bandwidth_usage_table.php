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
        Schema::create('tenant_bandwidth_usages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('router_id');
            $table->string('interface_name');
            $table->bigInteger('bytes_in');
            $table->bigInteger('bytes_out');
            $table->bigInteger('packets_in');
            $table->bigInteger('packets_out');
            $table->timestamp('timestamp');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_bandwidth_usages');
    }
};
