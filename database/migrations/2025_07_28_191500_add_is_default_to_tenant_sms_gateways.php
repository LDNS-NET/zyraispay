<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('tenant_sms_gateways', function (Blueprint $table) {
            $table->boolean('is_default')->default(false)->after('is_active');
        });
    }

    public function down()
    {
        Schema::table('tenant_sms_gateways', function (Blueprint $table) {
            $table->dropColumn('is_default');
        });
    }
};
