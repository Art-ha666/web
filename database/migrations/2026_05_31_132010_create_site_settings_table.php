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
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name')->default('AKH Solutions');
            $table->string('tagline')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->string('primary_email')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('telegram')->nullable();
            $table->string('address')->nullable();
            $table->json('socials')->nullable();
            $table->json('locations')->nullable();
            $table->foreignId('active_theme_id')->nullable()->constrained('themes')->nullOnDelete();
            $table->string('nav_cta_label')->default('Book a call');
            $table->string('nav_cta_url')->default('/contact');
            $table->text('footer_blurb')->nullable();
            $table->string('default_meta_title')->nullable();
            $table->text('default_meta_description')->nullable();
            $table->string('og_image')->nullable();
            $table->string('announcement_text')->nullable();
            $table->boolean('announcement_active')->default(false);
            $table->json('custom_tokens')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
