<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institucional', function (Blueprint $table) {
            $table->unsignedInteger('stat_categories_count')->default(30)->after('stat_employees_count');
            $table->unsignedSmallInteger('founded_year')->default(2008)->after('stat_categories_count');
            $table->json('history_milestones')->nullable()->after('founded_year');
            $table->unsignedTinyInteger('hero_autoplay_seconds')->default(6)->after('history_milestones');
        });

        Schema::table('page_translations', function (Blueprint $table) {
            $table->string('inst_hero_eyebrow')->nullable()->after('inst_section_one_content');
            $table->text('inst_hero_description')->nullable()->after('inst_hero_eyebrow');
            $table->string('inst_hero_cta')->nullable()->after('inst_hero_description');
            $table->string('inst_experiences_eyebrow')->nullable()->after('inst_hero_cta');
            $table->string('inst_experiences_title')->nullable()->after('inst_experiences_eyebrow');
            $table->text('inst_experiences_description')->nullable()->after('inst_experiences_title');
            $table->string('inst_banner_title')->nullable()->after('inst_experiences_description');
            $table->string('inst_stats_eyebrow')->nullable()->after('inst_banner_title');
            $table->string('inst_stats_title')->nullable()->after('inst_stats_eyebrow');
            $table->string('inst_gallery_eyebrow')->nullable()->after('inst_stats_title');
            $table->string('inst_gallery_title')->nullable()->after('inst_gallery_eyebrow');
            $table->string('inst_history_eyebrow')->nullable()->after('inst_gallery_title');
            $table->string('inst_history_title')->nullable()->after('inst_history_eyebrow');
            $table->text('inst_history_intro')->nullable()->after('inst_history_title');
            $table->string('inst_videos_eyebrow')->nullable()->after('inst_history_intro');
            $table->string('inst_videos_title')->nullable()->after('inst_videos_eyebrow');
            $table->text('inst_videos_description')->nullable()->after('inst_videos_title');
            $table->string('inst_cta_eyebrow')->nullable()->after('inst_videos_description');
            $table->string('inst_cta_title')->nullable()->after('inst_cta_eyebrow');
            $table->text('inst_cta_description')->nullable()->after('inst_cta_title');
            $table->string('inst_cta_button')->nullable()->after('inst_cta_description');
        });
    }

    public function down(): void
    {
        Schema::table('page_translations', function (Blueprint $table) {
            $table->dropColumn([
                'inst_hero_eyebrow', 'inst_hero_description', 'inst_hero_cta',
                'inst_experiences_eyebrow', 'inst_experiences_title', 'inst_experiences_description',
                'inst_banner_title', 'inst_stats_eyebrow', 'inst_stats_title',
                'inst_gallery_eyebrow', 'inst_gallery_title', 'inst_history_eyebrow',
                'inst_history_title', 'inst_history_intro', 'inst_videos_eyebrow',
                'inst_videos_title', 'inst_videos_description', 'inst_cta_eyebrow',
                'inst_cta_title', 'inst_cta_description', 'inst_cta_button',
            ]);
        });

        Schema::table('institucional', function (Blueprint $table) {
            $table->dropColumn([
                'stat_categories_count', 'founded_year',
                'history_milestones', 'hero_autoplay_seconds',
            ]);
        });
    }
};
