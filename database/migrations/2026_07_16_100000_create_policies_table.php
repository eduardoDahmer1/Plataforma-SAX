<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();
        DB::table('policies')->insert([
            [
                'title' => 'Política de Privacidade',
                'slug' => 'privacidade',
                'content' => '<h2>Privacidade e proteção de dados</h2><p>A SAX utiliza os dados informados pelo cliente para processar pedidos, pagamentos, entregas, prestar atendimento e cumprir obrigações legais.</p><h3>Dados coletados</h3><p>Podemos tratar dados cadastrais, de contato, endereço, histórico de compras, preferências e informações técnicas de acesso ao site.</p><h3>Compartilhamento</h3><p>Os dados podem ser compartilhados, no limite necessário, com empresas de pagamento, logística, tecnologia e autoridades competentes. Não comercializamos dados pessoais.</p><h3>Direitos do titular</h3><p>O cliente pode solicitar confirmação, acesso, correção ou exclusão de seus dados, observados os prazos e deveres legais de conservação, por meio da página de contato.</p><h3>Segurança e atualizações</h3><p>Adotamos medidas razoáveis de segurança. Esta política poderá ser atualizada e a versão vigente estará sempre disponível nesta página.</p>',
                'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'title' => 'Política de Compras e Vendas',
                'slug' => 'compras-e-vendas',
                'content' => '<h2>Condições de compra</h2><p>Ao finalizar uma compra, o cliente declara que os dados fornecidos são verdadeiros e que leu e aceitou as políticas vigentes.</p><h3>Preços e disponibilidade</h3><p>Preços, promoções e estoque são confirmados no momento da conclusão do pedido. Em caso de indisponibilidade ou erro evidente, entraremos em contato para oferecer solução ou cancelamento com restituição dos valores pagos.</p><h3>Pagamento</h3><p>O pedido somente será confirmado após a aprovação do pagamento. Para depósito ou transferência, a confirmação depende da validação do comprovante.</p><h3>Trocas, devoluções e cancelamentos</h3><p>As solicitações serão analisadas conforme a legislação aplicável, o estado do produto e os prazos informados no atendimento. O item deverá ser devolvido sem sinais de uso, com acessórios e embalagem, quando aplicável.</p><h3>Atendimento</h3><p>Dúvidas sobre pedidos, pagamentos, trocas ou cancelamentos podem ser enviadas pela página de contato.</p>',
                'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'title' => 'Política de Envios e Entregas',
                'slug' => 'envios-e-entregas',
                'content' => '<h2>Envios, entregas e retiradas</h2><p>O prazo e o custo de entrega dependem do destino, da modalidade escolhida e da disponibilidade dos produtos.</p><h3>Prazo</h3><p>A contagem do prazo começa após a confirmação do pagamento. Datas apresentadas no checkout são estimativas e podem sofrer alterações por eventos fora do controle da SAX.</p><h3>Endereço e recebimento</h3><p>É responsabilidade do cliente informar corretamente o endereço e garantir que haja pessoa autorizada para receber o pedido. Custos de uma nova tentativa por endereço incorreto ou ausência poderão ser cobrados.</p><h3>Conferência</h3><p>Recomendamos conferir a embalagem no recebimento e recusar o pedido se houver violação ou dano aparente, comunicando imediatamente o atendimento.</p><h3>Retirada em loja</h3><p>Quando disponível, a retirada será liberada após a confirmação do pedido. O responsável deverá apresentar documento e as informações solicitadas para identificação.</p>',
                'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('policies');
    }
};
