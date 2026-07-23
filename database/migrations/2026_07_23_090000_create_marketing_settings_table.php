<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('enabled')->default(true);
            $table->string('site_name')->nullable();
            $table->string('default_meta_title')->nullable();
            $table->text('default_meta_description')->nullable();
            $table->text('default_meta_keywords')->nullable();
            $table->string('robots')->default('index,follow');
            $table->string('canonical_base_url')->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->text('og_image_url')->nullable();
            $table->string('twitter_site')->nullable();
            $table->string('google_site_verification')->nullable();
            $table->string('bing_site_verification')->nullable();
            $table->string('meta_domain_verification')->nullable();
            $table->string('google_tag_manager_id')->nullable();
            $table->string('google_analytics_id')->nullable();
            $table->string('google_ads_id')->nullable();
            $table->string('google_ads_conversion_label')->nullable();
            $table->string('meta_pixel_id')->nullable();
            $table->string('tiktok_pixel_id')->nullable();
            $table->string('pinterest_tag_id')->nullable();
            $table->string('linkedin_partner_id')->nullable();
            $table->string('microsoft_clarity_id')->nullable();
            $table->text('organization_name')->nullable();
            $table->string('organization_url')->nullable();
            $table->text('organization_logo_url')->nullable();
            $table->text('organization_social_urls')->nullable();
            $table->longText('custom_head_scripts')->nullable();
            $table->longText('custom_body_start_scripts')->nullable();
            $table->longText('custom_body_end_scripts')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_settings');
    }
};
