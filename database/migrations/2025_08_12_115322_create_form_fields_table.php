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
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->onDelete('cascade');
            $table->string('name'); // техническое имя поля
            $table->string('label'); // отображаемое название
            $table->enum('type', ['text', 'email', 'phone', 'textarea', 'select', 'checkbox', 'radio', 'file', 'number', 'date', 'url']);
            $table->boolean('required')->default(false);
            $table->string('placeholder')->nullable();
            $table->json('options')->nullable(); // для select/radio/checkbox
            $table->json('validation_rules')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['site_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};
