<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('address');
            $table->string('phone_number');
            $table->string('email_address')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
            // This table is for tenant DB only, no tenant_id needed
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_leads');
    }
};
