<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TRANSLATION_KEYS = [
        'cafe_bistro_pjc',
        'cafe_bistro_asuncion',
        'cafe_bistro_choose_location',
        'cafe_bistro_admin_units',
    ];

    public function up(): void
    {
        Schema::table('cafe_bistros', function (Blueprint $table) {
            $table->string('name', 100)->nullable()->after('id');
            $table->string('slug', 100)->nullable()->unique()->after('name');
        });

        $source = DB::table('cafe_bistros')->orderBy('id')->first();

        if ($source) {
            DB::table('cafe_bistros')->where('id', $source->id)->update([
                'name' => 'Pedro Juan Caballero',
                'slug' => 'pedro-juan-caballero',
            ]);

            $asuncionId = DB::table('cafe_bistros')->where('slug', 'asuncion')->value('id');

            if (! $asuncionId) {
                $clone = (array) $source;
                unset($clone['id']);
                $clone['name'] = 'Asunción';
                $clone['slug'] = 'asuncion';
                $clone['created_at'] = now();
                $clone['updated_at'] = now();
                $asuncionId = DB::table('cafe_bistros')->insertGetId($clone);
            }

            $translations = DB::table('page_translations')
                ->where('page_type', 'App\\Models\\CafeBistro')
                ->where('page_id', $source->id)
                ->get();

            foreach ($translations as $translation) {
                $clone = (array) $translation;
                unset($clone['id']);
                $clone['page_id'] = $asuncionId;
                $clone['created_at'] = now();
                $clone['updated_at'] = now();

                DB::table('page_translations')->updateOrInsert(
                    [
                        'page_type' => $clone['page_type'],
                        'page_id' => $asuncionId,
                        'locale' => $clone['locale'],
                    ],
                    $clone
                );
            }
        }

        $translations = [
            ['cafe_bistro_pjc', 'Café & Bistrô PJC', 'Café & Bistro PJC', 'Café & Bistró PJC'],
            ['cafe_bistro_asuncion', 'Café & Bistrô Asunción', 'Café & Bistro Asunción', 'Café & Bistró Asunción'],
            ['cafe_bistro_choose_location', 'Escolha a unidade', 'Choose a location', 'Elige la sucursal'],
            ['cafe_bistro_admin_units', 'Unidades do Café & Bistrô', 'Café & Bistro locations', 'Sucursales de Café & Bistró'],
        ];

        foreach ($translations as $translation) {
            DB::table('languages')->updateOrInsert(
                ['key' => $translation[0]],
                [
                    'pt' => $translation[1],
                    'en' => $translation[2],
                    'es' => $translation[3],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        $asuncionId = DB::table('cafe_bistros')->where('slug', 'asuncion')->value('id');

        if ($asuncionId) {
            DB::table('page_translations')
                ->where('page_type', 'App\\Models\\CafeBistro')
                ->where('page_id', $asuncionId)
                ->delete();
            DB::table('cafe_bistros')->where('id', $asuncionId)->delete();
        }

        DB::table('languages')->whereIn('key', self::TRANSLATION_KEYS)->delete();

        Schema::table('cafe_bistros', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['name', 'slug']);
        });
    }
};
