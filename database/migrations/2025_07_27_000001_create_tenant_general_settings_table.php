<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tenant_general_settings', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            //$table->string('business_name')->nullable();
            $table->string('business_type')->nullable();
            $table->string('theme')->nullable();
            $table->string('deleted_at')->nullable();
            //$table->string('description')->nullable();
            // $table->string('registration_number')->nullable();
            // $table->string('tax_number')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('support_email')->nullable();
            $table->string('phone')->nullable();
            $table->string('support_phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->string('website')->nullable();
            $table->string('facebook')->nullable();
            $table->string('twitter')->nullable();
            $table->string('instagram')->nullable();
            $table->string('business_hours')->nullable();
            $table->string('timezone')->nullable();
            $table->string('currency')->nullable();
            $table->string('language')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->unique(['tenant_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('tenant_general_settings');
    }
};
