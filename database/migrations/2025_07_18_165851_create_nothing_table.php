<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('nothing', function (Blueprint $table) {
            $table->id();
            $table->string('phone');
            $table->foreignId('package_id')->constrained('packages')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending'); // pending, paid, failed
            $table->string('intasend_reference')->nullable();
            $table->string('intasend_checkout_id')->nullable();
            $table->json('response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('nothing');
    }
};
