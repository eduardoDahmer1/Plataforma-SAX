<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('generalsettings', function (Blueprint $table) {
            $table->json('home_sections')->nullable()->after('site_name');
        });

        DB::table('generalsettings')->orderBy('id')->each(function ($settings) {
            $enabled = static fn (string $column, bool $default = true): bool =>
                property_exists($settings, $column) ? (bool) $settings->{$column} : $default;

            DB::table('generalsettings')->where('id', $settings->id)->update([
                'home_sections' => json_encode([
                    'main_slider' => ['enabled' => true, 'position' => 1],
                    'categories' => ['enabled' => true, 'position' => 2],
                    'exclusive_collection' => ['enabled' => true, 'position' => 3],
                    'recent_products' => ['enabled' => $enabled('show_highlight_lancamentos'), 'position' => 4],
                    'editorial_banners' => ['enabled' => true, 'position' => 5],
                    'most_viewed' => ['enabled' => $enabled('show_highlight_famosos'), 'position' => 6],
                    'featured_products' => ['enabled' => $enabled('show_highlight_destaque'), 'position' => 7],
                    'brands' => ['enabled' => true, 'position' => 8],
                    'help' => ['enabled' => true, 'position' => 9],
                    'newsletter' => ['enabled' => true, 'position' => 10],
                ], JSON_THROW_ON_ERROR),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('generalsettings', function (Blueprint $table) {
            $table->dropColumn('home_sections');
        });
    }
};
