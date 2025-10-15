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
        Schema::create('tenant_sms', function (Blueprint $table) {
            $table->id();
            $table->string('recipient')->nullable();
            $table->string('recipient_phone');
            $table->text('message');
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->decimal('cost', 8, 2);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('sent_at')->nullable();
            $table->json('response')->nullable();
            $table->timestamps();
            // This table is for tenant DB only, no tenant_id needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_sms');
    }
};
