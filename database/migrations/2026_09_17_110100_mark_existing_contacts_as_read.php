<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('contacts')
            ->whereNull('read_at')
            ->update(['read_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        // O status de leitura é atividade do usuário e não deve ser apagado no rollback.
    }
};
