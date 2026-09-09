<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('home_banners', function (Blueprint $table) {
            $table->string('title_pt', 160)->nullable()->after('link');
            $table->text('description_pt')->nullable()->after('title_pt');
            $table->string('title_en', 160)->nullable()->after('description_pt');
            $table->text('description_en')->nullable()->after('title_en');
            $table->string('title_es', 160)->nullable()->after('description_en');
            $table->text('description_es')->nullable()->after('title_es');
        });

        $fallback = [
            'pt' => ['title' => 'Campanhas em destaque', 'description' => 'Descubra a curadoria e as novidades selecionadas pela SAX.'],
            'en' => ['title' => 'Featured campaigns', 'description' => 'Discover the curation and latest selections from SAX.'],
            'es' => ['title' => 'Campañas destacadas', 'description' => 'Descubre la curaduría y las novedades seleccionadas por SAX.'],
        ];

        $settings = DB::table('generalsettings')->orderBy('id')->first();
        $storedSections = json_decode((string) ($settings->home_sections ?? ''), true);
        $mainContent = is_array($storedSections['main_slider']['content'] ?? null)
            ? $storedSections['main_slider']['content']
            : $fallback;

        $values = [];
        foreach (['pt', 'en', 'es'] as $language) {
            $values["title_{$language}"] = $mainContent[$language]['title'] ?? $fallback[$language]['title'];
            $values["description_{$language}"] = $mainContent[$language]['description'] ?? $fallback[$language]['description'];
        }

        DB::table('home_banners')->where('group', 'main')->update($values);
    }

    public function down(): void
    {
        Schema::table('home_banners', function (Blueprint $table) {
            $table->dropColumn([
                'title_pt', 'description_pt',
                'title_en', 'description_en',
                'title_es', 'description_es',
            ]);
        });
    }
};
