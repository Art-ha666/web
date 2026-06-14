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
        Schema::create('nav_items', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('url')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('nav_items')->cascadeOnDelete();
            $table->string('location')->default('header');
            $table->string('mega_group')->nullable();
            $table->string('description')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_cta')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nav_items');
    }
};
