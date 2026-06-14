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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('client_name')->nullable();
            $table->string('client_type')->nullable();
            $table->string('industry')->nullable();
            $table->string('year')->nullable();
            $table->json('category_tags')->nullable();
            $table->string('headline_result')->nullable();
            $table->text('summary')->nullable();
            $table->longText('challenge')->nullable();
            $table->longText('approach')->nullable();
            $table->longText('architecture_notes')->nullable();
            $table->json('results')->nullable();
            $table->string('cover_image')->nullable();
            $table->json('gallery')->nullable();
            $table->string('video_url')->nullable();
            $table->json('tech_stack')->nullable();
            $table->foreignId('related_service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->boolean('featured')->default(false);
            $table->boolean('is_published')->default(true);
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
        Schema::dropIfExists('projects');
    }
};
