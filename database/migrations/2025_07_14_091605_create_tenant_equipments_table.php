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
        Schema::create('tenant_equipments', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('type');
        $table->string('serial_number')->unique();
        $table->string('location')->nullable();
        $table->string('model')->nullable();
        $table->decimal('price', 10, 2)->nullable();
        $table->decimal('total_price', 10, 2)->nullable();
        $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
        $table->string('assigned_to')->nullable();
        $table->timestamps();
        // This table is for tenant DB only, no tenant_id needed
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_equipments');
    }
};
