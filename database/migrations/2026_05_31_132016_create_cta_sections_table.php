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
        Schema::create('cta_sections', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('eyebrow')->nullable();
            $table->string('headline');
            $table->text('body')->nullable();
            $table->string('primary_cta_label')->nullable();
            $table->string('primary_cta_url')->nullable();
            $table->string('secondary_cta_label')->nullable();
            $table->string('secondary_cta_url')->nullable();
            $table->string('microcopy')->nullable();
            $table->string('gradient_variant')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cta_sections');
    }
};
