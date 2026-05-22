<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_requests', function (Blueprint $table) {
            $table->string('lead_type', 64)->nullable()->after('form_data');
            $table->enum('crm_status', ['pending', 'sent', 'failed', 'skipped'])
                ->default('pending')
                ->after('lead_type');
            $table->string('crm_external_id', 128)->nullable()->after('crm_status');
            $table->unsignedTinyInteger('crm_attempts')->default(0)->after('crm_external_id');
            $table->timestamp('crm_sent_at')->nullable()->after('crm_attempts');
            $table->text('crm_last_error')->nullable()->after('crm_sent_at');

            $table->index('crm_status');
            $table->index('crm_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('form_requests', function (Blueprint $table) {
            $table->dropIndex(['crm_status']);
            $table->dropIndex(['crm_sent_at']);
            $table->dropColumn([
                'lead_type',
                'crm_status',
                'crm_external_id',
                'crm_attempts',
                'crm_sent_at',
                'crm_last_error',
            ]);
        });
    }
};
