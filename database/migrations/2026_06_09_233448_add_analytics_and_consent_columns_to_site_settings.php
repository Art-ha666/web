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
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('ga_measurement_id', 40)->nullable()->after('custom_tokens');
            $table->text('head_scripts')->nullable()->after('ga_measurement_id');
            $table->string('newsletter_heading', 120)->nullable()->after('head_scripts');
            $table->string('newsletter_placeholder', 120)->nullable()->after('newsletter_heading');
            $table->string('newsletter_success', 200)->nullable()->after('newsletter_placeholder');
            $table->text('cookie_banner_text')->nullable()->after('newsletter_success');
            $table->string('cookie_accept_label', 60)->nullable()->after('cookie_banner_text');
            $table->string('cookie_decline_label', 60)->nullable()->after('cookie_accept_label');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'ga_measurement_id',
                'head_scripts',
                'newsletter_heading',
                'newsletter_placeholder',
                'newsletter_success',
                'cookie_banner_text',
                'cookie_accept_label',
                'cookie_decline_label',
            ]);
        });
    }
};
