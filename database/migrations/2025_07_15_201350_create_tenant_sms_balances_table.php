<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tenant_sms_balances', function (Blueprint $table) {
            $table->id(); // Use auto-incrementing id for tenant DB
            $table->decimal('balance', 8, 2)->default(0);
            $table->timestamp('last_topped_up_at')->nullable();
            $table->timestamps();
            // This table is for tenant DB only, no tenant_id needed
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_sms_balances');
    }
};
