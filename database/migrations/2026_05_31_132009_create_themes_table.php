<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->json('tokens');
            $table->string('hero_variant')->default('aurora');
            $table->string('layout_variant')->default('standard');
            $table->string('preview_image')->nullable();
            $table->boolean('uses_three')->default(false);
            $table->boolean('is_premium')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('themes');
    }
};
