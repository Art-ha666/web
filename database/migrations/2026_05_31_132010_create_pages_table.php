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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->json('blocks')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('og_image')->nullable();
            $table->string('status')->default('draft');
            $table->boolean('is_system')->default(false);
            $table->boolean('show_in_nav')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
