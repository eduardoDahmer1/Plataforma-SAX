<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const KEYS = [
        'cafe_hero_eyebrow_pjc',
        'cafe_hero_eyebrow_asuncion',
    ];

    public function up(): void
    {
        $now = now();

        DB::table('languages')->updateOrInsert(
            ['key' => 'cafe_hero_eyebrow_pjc'],
            [
                'pt' => 'SAX Café & Bistrô · PJC',
                'es' => 'SAX Café & Bistrô · PJC',
                'en' => 'SAX Café & Bistrô · PJC',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('languages')->updateOrInsert(
            ['key' => 'cafe_hero_eyebrow_asuncion'],
            [
                'pt' => 'SAX Café & Bistrô · ASUNCION',
                'es' => 'SAX Café & Bistrô · ASUNCION',
                'en' => 'SAX Café & Bistrô · ASUNCION',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    public function down(): void
    {
        DB::table('languages')->whereIn('key', self::KEYS)->delete();
    }
};
