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
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('department')->nullable();
            $table->string('location')->nullable();
            $table->string('employment_type')->nullable();
            $table->string('seniority')->nullable();
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->json('responsibilities')->nullable();
            $table->json('requirements')->nullable();
            $table->json('tech_stack')->nullable();
            $table->string('salary_range')->nullable();
            $table->boolean('is_open')->default(true);
            $table->string('apply_url')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_postings');
    }
};
