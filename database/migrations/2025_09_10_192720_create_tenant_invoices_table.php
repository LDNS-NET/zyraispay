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
        Schema::create('tenant_invoices', function (Blueprint $table) {
            $table->id();
             $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('user');
            $table->decimal('amount', 10, 2);
            $table->string('tenant_invoice');
            $table->string('package');
            $table->date('issued_on');
            $table->date('due_on');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_invoices');
    }
};
