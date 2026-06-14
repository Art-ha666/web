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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('business_email');
            $table->string('company')->nullable();
            $table->string('phone')->nullable();
            $table->string('budget_range')->nullable();
            $table->string('service_interest')->nullable();
            $table->text('message')->nullable();
            $table->string('source_page')->nullable();
            $table->boolean('consent_marketing')->default(false);
            $table->boolean('consent_data_processing')->default(false);
            $table->string('status')->default('new');
            $table->text('admin_notes')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
