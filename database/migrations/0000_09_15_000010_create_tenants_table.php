<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            // your custom columns may go here
            $table->string('id')->primary();
            $table->string('business_name'); // Business/tenant name
            $table->string('username');      // Tenant admin username
            $table->string('email');         // Tenant admin email
            $table->string('phone');         // Tenant admin phone
            $table->string('wallet_id')->nullable();
            $table->decimal('wallet_balance', 12, 2)->default(0); // Local account balance
            $table->timestamps();
            $table->json('data')->nullable();
            // This table is for the central DB, storing tenant records
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
}
