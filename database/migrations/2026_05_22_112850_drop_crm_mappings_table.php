<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('crm_mappings');
    }

    public function down(): void
    {
        Schema::create('crm_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->onDelete('cascade');
            $table->string('crm_field');
            $table->text('mapping_value');
            $table->enum('value_type', ['field', 'static', 'template'])->default('field');
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->unique(['site_id', 'crm_field']);
        });
    }
};
