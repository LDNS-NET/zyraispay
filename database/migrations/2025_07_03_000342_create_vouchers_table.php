<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('type', ['percentage', 'fixed', 'data'])->default('data'); // default to 'data' for package vouchers
            $table->string('value'); // Can be price, discount, GBs
            $table->enum('status', ['active', 'used', 'expired', 'revoked'])->default('active');
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_used')->default(false);

            // NEW: Link to the package that defines the value/validity
            $table->foreignId('package_id')->constrained()->onDelete('cascade');

            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('used_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('sent_to')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('sent_at')->nullable();
            $table->text('note')->nullable();

            $table->timestamps();
            // This table is for tenant DB only, no tenant_id needed
        });
    }

    public function down(): void {
        Schema::dropIfExists('vouchers');
    }
};
