<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tenant_whatsapp_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('provider');
            $table->string('api_key')->nullable(); // Store encrypted in model
            $table->string('api_secret')->nullable(); // Store encrypted in model
            $table->string('phone_number')->nullable();
            $table->string('webhook_url')->nullable();
            $table->string('status_callback_url')->nullable();
            $table->string('region')->nullable();
            $table->json('custom_parameters')->nullable();
            $table->boolean('is_active')->default(false);
            $table->string('label')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('tenant_whatsapp_gateways');
    }
};
