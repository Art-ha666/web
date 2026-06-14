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
        Schema::table('site_settings', function (Blueprint $table): void {
            $table->boolean('ai_blog_enabled')->default(false);
            $table->string('ai_provider')->default('openai');
            $table->string('ai_blog_frequency')->default('weekly');
            $table->unsignedSmallInteger('ai_blog_per_run')->default(1);
            $table->text('ai_blog_topics')->nullable();
            $table->boolean('ai_blog_autopublish')->default(false);
        });

        Schema::table('articles', function (Blueprint $table): void {
            $table->string('generated_by')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'ai_blog_enabled',
                'ai_provider',
                'ai_blog_frequency',
                'ai_blog_per_run',
                'ai_blog_topics',
                'ai_blog_autopublish',
            ]);
        });

        Schema::table('articles', function (Blueprint $table): void {
            $table->dropColumn('generated_by');
        });
    }
};
