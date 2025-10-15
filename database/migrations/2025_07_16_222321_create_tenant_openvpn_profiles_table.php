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
        Schema::create('tenant_openvpn_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('config_path');
            $table->string('client_cert_path');
            $table->string('client_key_path');
            $table->string('ca_cert_path');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_openvpn_profiles');
    }
};
