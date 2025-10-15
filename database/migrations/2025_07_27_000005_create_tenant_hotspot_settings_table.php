<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tenant_hotspot_settings', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('portal_template')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('user_prefix')->nullable();
            $table->integer('prune_inactive_days')->nullable();
            $table->string('description')->nullable();
            $table->json('allowed_networks')->nullable();
            $table->unsignedBigInteger('default_package_id')->nullable();
            $table->string('captive_portal_url')->nullable();
            $table->json('advanced_options')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('last_updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->unique(['tenant_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('tenant_hotspot_settings');
    }
};
