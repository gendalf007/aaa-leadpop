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
        Schema::create('form_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained()->onDelete('cascade');
            $table->json('form_data'); // JSON с данными всех полей
            $table->string('source')->default('form'); // источник заявки
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_requests');
    }
};
