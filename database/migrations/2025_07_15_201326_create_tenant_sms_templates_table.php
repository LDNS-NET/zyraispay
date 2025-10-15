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
        Schema::create('tenant_sms_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('content'); // Use {{placeholders}} in templates
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            // This table is for tenant DB only, no tenant_id needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_sms_templates');
    }
};
