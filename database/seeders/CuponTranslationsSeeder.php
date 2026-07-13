<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

/**
 * Textos do módulo de cupons (carrinho, checkout, selo do produto, painel e área do cliente).
 * As traduções ficam na tabela `languages` e são carregadas pelo AppServiceProvider.
 */
class CuponTranslationsSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->linhas() as $key => [$pt, $es, $en]) {
            Language::updateOrCreate(['key' => $key], ['pt' => $pt, 'es' => $es, 'en' => $en]);
        }

        Cache::forget('all_translations_db');
    }

    /** @return array<string, array{0:string,1:string,2:string}> [chave => [pt, es, en]] */
    private function linhas(): array
    {
        return [
            // Mensagens de validação do cupom
            'cupon_invalido'        => ['Cupom inválido ou inexistente.', 'Cupón inválido o inexistente.', 'Invalid or unknown coupon.'],
            'cupon_inativo'         => ['Este cupom não está ativo.', 'Este cupón no está activo.', 'This coupon is not active.'],
            'cupon_expirado'        => ['Este cupom está fora do período de validade.', 'Este cupón está fuera del período de validez.', 'This coupon is outside its validity period.'],
            'cupon_esgotado'        => ['Este cupom já atingiu o limite de usos.', 'Este cupón alcanzó el límite de usos.', 'This coupon reached its usage limit.'],
            'cupon_limite_usuario'  => ['Você já utilizou este cupom o número máximo de vezes.', 'Ya utilizaste este cupón el número máximo de veces.', 'You have already used this coupon the maximum number of times.'],
            'cupon_carrinho_vazio'  => ['Adicione produtos ao carrinho para usar um cupom.', 'Agrega productos al carrito para usar un cupón.', 'Add products to your cart to use a coupon.'],
            'cupon_valor_minimo'    => ['Este cupom vale para compras a partir de :valor.', 'Este cupón es válido para compras desde :valor.', 'This coupon applies to orders from :valor.'],
            'cupon_sem_itens'       => ['Nenhum produto do seu carrinho é elegível para este cupom.', 'Ningún producto de tu carrito es elegible para este cupón.', 'No product in your cart is eligible for this coupon.'],
            'cupon_sem_itens_preco' => ['Este cupom só vale para produtos de até :valor.', 'Este cupón solo es válido para productos de hasta :valor.', 'This coupon only applies to products up to :valor.'],
            'cupon_sem_desconto'    => ['Este cupom não gera desconto no seu carrinho.', 'Este cupón no genera descuento en tu carrito.', 'This coupon generates no discount on your cart.'],
            'cupon_aplicado'        => ['Cupom aplicado com sucesso!', '¡Cupón aplicado con éxito!', 'Coupon applied successfully!'],
            'cupon_removido'        => ['Cupom removido.', 'Cupón eliminado.', 'Coupon removed.'],
            'cupon_precisa_login'   => ['Faça login para usar cupons.', 'Inicia sesión para usar cupones.', 'Sign in to use coupons.'],
            'cupon_erro_conexao'    => ['Erro de conexão. Tente novamente.', 'Error de conexión. Intenta de nuevo.', 'Connection error. Please try again.'],
            'cupon_digite_codigo'   => ['Digite o código do cupom.', 'Escribe el código del cupón.', 'Enter the coupon code.'],

            // Escopo do cupom
            'cupon_escopo_geral'     => ['Válido em todo o site', 'Válido en todo el sitio', 'Valid sitewide'],
            'cupon_escopo_categoria' => ['Categoria: :nome', 'Categoría: :nome', 'Category: :nome'],
            'cupon_escopo_marca'     => ['Marca: :nome', 'Marca: :nome', 'Brand: :nome'],
            'cupon_escopo_produto'   => ['Produto: :nome', 'Producto: :nome', 'Product: :nome'],
            'cupon_escopo_nome'      => ['Produtos com ":termo" no nome', 'Productos con ":termo" en el nombre', 'Products with ":termo" in the name'],

            // Carrinho e checkout
            'cupon_tem_codigo'     => ['Tem um cupom de desconto?', '¿Tienes un cupón de descuento?', 'Have a discount coupon?'],
            'cupon_placeholder'    => ['Digite seu cupom', 'Escribe tu cupón', 'Enter your coupon'],
            'cupon_aplicar_btn'    => ['Aplicar', 'Aplicar', 'Apply'],
            'cupon_remover_btn'    => ['Remover cupom', 'Eliminar cupón', 'Remove coupon'],
            'cupon_aplicado_label' => ['Cupom aplicado', 'Cupón aplicado', 'Coupon applied'],

            // Selo no card e na página do produto
            'cupon_selo_card_label'    => ['com cupom', 'con cupón', 'with coupon'],
            'cupon_selo_produto_texto' => ['com o cupom', 'con el cupón', 'with coupon'],

            // Área do cliente
            'cupon_meus_cupons_titulo' => ['Meus cupons', 'Mis cupones', 'My coupons'],
            'cupon_meus_cupons_desc'   => ['Aplique um cupom e aproveite suas melhores condições.', 'Aplica un cupón y aprovecha tus mejores condiciones.', 'Apply a coupon and enjoy your best deals.'],
            'cupon_codigo_label'       => ['Código do cupom', 'Código del cupón', 'Coupon code'],
            'cupon_aplicar_ajuda'      => ['O cupom fica guardado no seu carrinho até você finalizar a compra.', 'El cupón queda guardado en tu carrito hasta que finalices la compra.', 'The coupon stays in your cart until you complete the purchase.'],
            'cupon_disponiveis_titulo' => ['Cupons disponíveis', 'Cupones disponibles', 'Available coupons'],
            'cupon_nenhum_disponivel'  => ['Nenhum cupom disponível no momento.', 'Ningún cupón disponible por ahora.', 'No coupons available right now.'],
            'cupon_usar_btn'           => ['Usar este cupom', 'Usar este cupón', 'Use this coupon'],
            'cupon_em_uso_btn'         => ['Em uso', 'En uso', 'In use'],
            'cupon_ir_carrinho_btn'    => ['Ir ao carrinho', 'Ir al carrito', 'Go to cart'],
            'cupon_valido_ate'         => ['Válido até :data', 'Válido hasta :data', 'Valid until :data'],
            'cupon_min_compra'         => ['Compra mínima :valor', 'Compra mínima :valor', 'Minimum order :valor'],
            'cupon_max_desconto'       => ['Desconto máximo :valor', 'Descuento máximo :valor', 'Maximum discount :valor'],
            'cupon_max_preco_produto'  => ['Produtos até :valor', 'Productos hasta :valor', 'Products up to :valor'],

            // Painel: mensagens
            'cupon_criado_sucesso'     => ['Cupom criado com sucesso!', '¡Cupón creado con éxito!', 'Coupon created successfully!'],
            'cupon_atualizado_sucesso' => ['Cupom atualizado com sucesso!', '¡Cupón actualizado con éxito!', 'Coupon updated successfully!'],
            'cupon_deletado_sucesso'   => ['Cupom excluído com sucesso!', '¡Cupón eliminado con éxito!', 'Coupon deleted successfully!'],
            'cupon_desativado_em_uso'  => ['Este cupom já foi usado em pedidos, então foi desativado em vez de excluído.', 'Este cupón ya fue usado en pedidos, por eso fue desactivado en lugar de eliminado.', 'This coupon was already used in orders, so it was deactivated instead of deleted.'],
            'cupon_ativado'            => ['Cupom ativado.', 'Cupón activado.', 'Coupon activated.'],
            'cupon_desativado'         => ['Cupom desativado.', 'Cupón desactivado.', 'Coupon deactivated.'],
            'cupon_erro_criar'         => ['Não foi possível criar o cupom.', 'No fue posible crear el cupón.', 'Could not create the coupon.'],
            'cupon_erro_atualizar'     => ['Não foi possível atualizar o cupom.', 'No fue posible actualizar el cupón.', 'Could not update the coupon.'],
            'cupon_codigo_formato'     => ['O código deve conter apenas letras, números, hífen ou underline.', 'El código debe contener solo letras, números, guion o guion bajo.', 'The code may contain only letters, numbers, hyphen or underscore.'],
            'cupon_exige_categoria'    => ['Selecione uma categoria para este cupom.', 'Selecciona una categoría para este cupón.', 'Select a category for this coupon.'],
            'cupon_exige_marca'        => ['Selecione uma marca para este cupom.', 'Selecciona una marca para este cupón.', 'Select a brand for this coupon.'],
            'cupon_exige_produto'      => ['Selecione um produto para este cupom.', 'Selecciona un producto para este cupón.', 'Select a product for this coupon.'],
            'cupon_exige_nome'         => ['Informe o termo do nome do produto.', 'Indica el término del nombre del producto.', 'Enter the product name term.'],
            'cupon_percentual_max'     => ['O percentual não pode passar de 100%.', 'El porcentaje no puede superar el 100%.', 'The percentage cannot exceed 100%.'],

            // Painel: formulário
            'cupon_ativo_label'                => ['Cupom ativo', 'Cupón activo', 'Active coupon'],
            'cupon_descricao_label'            => ['Descrição interna', 'Descripción interna', 'Internal description'],
            'cupon_descricao_placeholder'      => ['Ex: campanha de inverno', 'Ej: campaña de invierno', 'E.g. winter campaign'],
            'cupon_codigo_ajuda'               => ['Letras, números, hífen ou underline. Salvo em maiúsculas.', 'Letras, números, guion o guion bajo. Se guarda en mayúsculas.', 'Letters, numbers, hyphen or underscore. Saved in uppercase.'],
            'cupon_montante_ajuda_percentual'  => ['Percentual de desconto (1 a 100).', 'Porcentaje de descuento (1 a 100).', 'Discount percentage (1 to 100).'],
            'cupon_montante_ajuda_valor'       => ['Valor fixo abatido do pedido, uma única vez.', 'Valor fijo descontado del pedido, una sola vez.', 'Fixed amount deducted once from the order.'],
            'cupon_ilimitado'                  => ['Ilimitado', 'Ilimitado', 'Unlimited'],
            'cupon_quantidade_ajuda'           => ['Deixe vazio para uso ilimitado.', 'Déjalo vacío para uso ilimitado.', 'Leave empty for unlimited use.'],
            'cupon_ja_usado'                   => ['Já usado :n vez(es).', 'Ya usado :n vez(ces).', 'Already used :n time(s).'],
            'cupon_limite_usuario_label'       => ['Usos por cliente', 'Usos por cliente', 'Uses per customer'],
            'cupon_limite_usuario_ajuda'       => ['Quantas vezes o mesmo cliente pode usar. Vazio = ilimitado.', 'Cuántas veces puede usarlo el mismo cliente. Vacío = ilimitado.', 'How many times the same customer can use it. Empty = unlimited.'],
            'cupon_regras_valor_sec'           => ['Regras de valor', 'Reglas de valor', 'Value rules'],
            'cupon_valor_minimo_ajuda'         => ['Subtotal mínimo do pedido para liberar o cupom.', 'Subtotal mínimo del pedido para habilitar el cupón.', 'Minimum order subtotal to unlock the coupon.'],
            'cupon_desconto_maximo_label'      => ['Desconto máximo', 'Descuento máximo', 'Maximum discount'],
            'cupon_desconto_maximo_ajuda'      => ['Teto do desconto gerado, mesmo em percentual alto.', 'Tope del descuento generado, incluso con porcentaje alto.', 'Cap on the generated discount, even with a high percentage.'],
            'cupon_preco_maximo_produto_label' => ['Preço máximo do produto', 'Precio máximo del producto', 'Maximum product price'],
            'cupon_preco_maximo_produto_ajuda' => ['Só produtos até esse preço recebem o desconto.', 'Solo los productos hasta ese precio reciben el descuento.', 'Only products up to this price get the discount.'],
            'cupon_por_nome_opt'               => ['Por nome do produto', 'Por nombre del producto', 'By product name'],
            'cupon_nome_termo_label'           => ['Termo no nome do produto', 'Término en el nombre del producto', 'Term in the product name'],
            'cupon_nome_termo_placeholder'     => ['Ex: tênis', 'Ej: zapatilla', 'E.g. sneaker'],
            'cupon_nome_termo_ajuda'           => ['O desconto vale para todo produto que tenha esse termo no nome.', 'El descuento vale para todo producto que tenga ese término en el nombre.', 'The discount applies to every product with this term in its name.'],

            // Painel: listagem e detalhe
            'cupon_buscar_label'            => ['Buscar por código', 'Buscar por código', 'Search by code'],
            'cupon_buscar_placeholder'      => ['Ex: PROMO2026', 'Ej: PROMO2026', 'E.g. PROMO2026'],
            'cupon_filtrar_btn'             => ['Filtrar', 'Filtrar', 'Filter'],
            'cupon_situacao_label'          => ['Situação', 'Situación', 'Status'],
            'cupon_situacao_todas'          => ['Todas', 'Todas', 'All'],
            'cupon_situacao_vigente'        => ['Vigente', 'Vigente', 'Active'],
            'cupon_situacao_agendado'       => ['Agendado', 'Programado', 'Scheduled'],
            'cupon_situacao_expirado'       => ['Expirado', 'Expirado', 'Expired'],
            'cupon_situacao_inativo'        => ['Inativo', 'Inactivo', 'Inactive'],
            'cupon_situacao_esgotado'       => ['Esgotado', 'Agotado', 'Used up'],
            'cupon_vigencia_label'          => ['Vigência', 'Vigencia', 'Validity'],
            'cupon_usos_label'              => ['Usos', 'Usos', 'Uses'],
            'cupon_usos_restantes_label'    => ['Usos restantes', 'Usos restantes', 'Remaining uses'],
            'cupon_desconto_label'          => ['Desconto', 'Descuento', 'Discount'],
            'cupon_total_descontado_label'  => ['Total descontado', 'Total descontado', 'Total discounted'],
            'cupon_ver_btn'                 => ['Ver', 'Ver', 'View'],
            'cupon_ativar_btn'              => ['Ativar', 'Activar', 'Activate'],
            'cupon_desativar_btn'           => ['Desativar', 'Desactivar', 'Deactivate'],
            'cupon_historico_uso_titulo'    => ['Histórico de uso', 'Historial de uso', 'Usage history'],
            'cupon_sem_uso_ainda'           => ['Este cupom ainda não foi usado.', 'Este cupón todavía no fue usado.', 'This coupon has not been used yet.'],
            'cupon_col_cliente'             => ['Cliente', 'Cliente', 'Customer'],
            'cupon_col_pedido'              => ['Pedido', 'Pedido', 'Order'],
            'cupon_col_desconto'            => ['Desconto', 'Descuento', 'Discount'],
            'cupon_col_data'                => ['Data', 'Fecha', 'Date'],
        ];
    }
}
