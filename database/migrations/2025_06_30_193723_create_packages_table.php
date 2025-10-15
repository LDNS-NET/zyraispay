<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['hotspot', 'pppoe', 'static']);
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('device_limit')->nullable();
            $table->integer('duration_value');
            $table->string('duration_unit');
            $table->integer('upload_speed')->comment('mbps');
            $table->integer('download_speed')->comment('mbps');
            $table->integer('burst_limit')->nullable()->comment('optional burst speed in kbps');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            // This table is for tenant DB only, no tenant_id needed
        });
    }

    public function down(): void {
        Schema::dropIfExists('packages');
    }
};
