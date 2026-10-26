<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('orders')
            ->whereNull('read_at')
            ->update(['read_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        // O status de leitura representa atividade administrativa e não deve ser apagado.
    }
};
