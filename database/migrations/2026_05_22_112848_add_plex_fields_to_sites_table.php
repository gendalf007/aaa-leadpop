<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->unsignedBigInteger('plex_dealer_id')->nullable()->after('api_key');
            $table->unsignedBigInteger('plex_website_id')->nullable()->after('plex_dealer_id');
            $table->string('plex_website_host', 255)->nullable()->after('plex_website_id');
            $table->json('allowed_lead_types')->nullable()->after('plex_website_host');
            $table->string('default_lead_type', 64)->nullable()->after('allowed_lead_types');
        });
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn([
                'plex_dealer_id',
                'plex_website_id',
                'plex_website_host',
                'allowed_lead_types',
                'default_lead_type',
            ]);
        });
    }
};
