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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->string('tab_label')->nullable();
            $table->string('short_blurb')->nullable();
            $table->text('intro')->nullable();
            $table->string('value_metric')->nullable();
            $table->json('benefit_bullets')->nullable();
            $table->longText('detail_body')->nullable();
            $table->json('gradient')->nullable();
            $table->string('demo_media')->nullable();
            $table->json('tech_stack')->nullable();
            $table->boolean('featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
