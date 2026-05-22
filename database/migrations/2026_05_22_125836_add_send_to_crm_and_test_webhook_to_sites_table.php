<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->boolean('send_to_crm')->default(true)->after('default_lead_type');
            $table->string('test_webhook_url', 255)->nullable()->after('send_to_crm');
        });
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn(['send_to_crm', 'test_webhook_url']);
        });
    }
};
