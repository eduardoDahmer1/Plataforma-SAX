<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Agregar la nueva columna JSON
        Schema::table('bridals', function (Blueprint $table) {
            $table->json('locations')->nullable()->after('social_instagram');
        });

        // 2. Migrar datos: convertir los 8 campos sueltos a un JSON
        $records = DB::table('bridals')->get();

        foreach ($records as $record) {
            $locations = [];

            if ($record->branch_asuncion_name) {
                $locations[] = [
                    'name'         => $record->branch_asuncion_name,
                    'address'      => $record->branch_asuncion_address,
                    'whatsapp_url' => $this->formatWhatsapp($record->branch_asuncion_phone),
                    'image'        => $record->branch_asuncion_image,
                ];
            }

            if ($record->branch_cde_name) {
                $locations[] = [
                    'name'         => $record->branch_cde_name,
                    'address'      => $record->branch_cde_address,
                    'whatsapp_url' => $this->formatWhatsapp($record->branch_cde_phone),
                    'image'        => $record->branch_cde_image,
                ];
            }

            DB::table('bridals')
                ->where('id', $record->id)
                ->update(['locations' => json_encode($locations)]);
        }

        // 3. Eliminar los 8 campos huérfanos
        Schema::table('bridals', function (Blueprint $table) {
            $table->dropColumn([
                'branch_asuncion_name', 'branch_asuncion_address',
                'branch_asuncion_phone', 'branch_asuncion_image',
                'branch_cde_name', 'branch_cde_address',
                'branch_cde_phone', 'branch_cde_image',
            ]);
        });
    }

    public function down(): void
    {
        // Rollback no implementado: migración destructiva intencional
    }

    private function formatWhatsapp(?string $phone): ?string
    {
        if (!$phone) return null;
        $clean = preg_replace('/[^0-9]/', '', $phone);
        return 'https://wa.me/' . $clean;
    }
};
