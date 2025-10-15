<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tenant_payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('provider');
            $table->string('api_key')->nullable(); // Store encrypted in model
            $table->string('payout_method')->nullable();
            $table->boolean('is_active')->default(false);
            $table->string('label')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('tenant_payment_gateways');
    }
};
