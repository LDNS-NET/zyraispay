<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tenant_settings', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id'); // Match tenants.id type
            $table->string('category'); // payout, sms_gateway, etc.
            $table->json('settings');
            $table->unsignedBigInteger('created_by')->nullable(); // User who created/updated
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->unique(['tenant_id', 'category']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('tenant_settings');
    }
};
