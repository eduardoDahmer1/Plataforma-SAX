<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cupons', function (Blueprint $table) {
            $table->boolean('ativo')->default(1)->after('codigo');
            $table->string('descricao')->nullable()->after('ativo');
            $table->string('nome_termo')->nullable()->after('produto_id');
            $table->decimal('desconto_maximo', 10, 2)->nullable()->after('valor_minimo');
            $table->decimal('preco_maximo_produto', 10, 2)->nullable()->after('desconto_maximo');
            $table->unsignedInteger('limite_por_usuario')->nullable()->after('quantidade');
        });

        // valor_maximo era usado como teto de desconto no fluxo do carrinho.
        DB::table('cupons')->whereNotNull('valor_maximo')->update([
            'desconto_maximo' => DB::raw('valor_maximo'),
        ]);

        Schema::table('cupons', function (Blueprint $table) {
            $table->dropColumn('valor_maximo');
        });

        // 'modelo' era um enum; agora aceita tambem 'nome' (termo no nome do produto).
        DB::statement("ALTER TABLE cupons MODIFY modelo VARCHAR(20) NULL");

        Schema::table('user_cupons', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->nullable()->after('cupon_id');
            $table->timestamp('usado_em')->nullable()->after('desconto');

            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
            $table->index(['user_id', 'cupon_id']);
        });
    }

    public function down(): void
    {
        Schema::table('user_cupons', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropIndex(['user_id', 'cupon_id']);
            $table->dropColumn(['order_id', 'usado_em']);
        });

        Schema::table('cupons', function (Blueprint $table) {
            $table->decimal('valor_maximo', 10, 2)->nullable()->after('valor_minimo');
        });

        DB::table('cupons')->whereNotNull('desconto_maximo')->update([
            'valor_maximo' => DB::raw('desconto_maximo'),
        ]);

        Schema::table('cupons', function (Blueprint $table) {
            $table->dropColumn([
                'ativo', 'descricao', 'nome_termo',
                'desconto_maximo', 'preco_maximo_produto', 'limite_por_usuario',
            ]);
        });

        DB::statement("ALTER TABLE cupons MODIFY modelo ENUM('categoria','produto','marca') NULL");
    }
};
