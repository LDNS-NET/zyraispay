<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tenant_notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('type'); // expiry, payment, mikrotik, etc.
            $table->string('channel'); // sms, whatsapp, email
            $table->string('channel_type')->nullable(); // for future expansion (transactional, marketing, etc.)
            $table->string('template_type')->default('user'); // user/system
            $table->string('subject')->nullable();
            $table->text('message_template');
            $table->json('variables')->nullable();
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('tenant_notification_templates');
    }
};
