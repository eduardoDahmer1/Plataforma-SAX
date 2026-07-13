<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cupon extends Model
{
    use HasFactory;

    public const MODELOS = ['geral', 'categoria', 'marca', 'produto', 'nome'];

    protected $fillable = [
        'codigo',
        'ativo',
        'descricao',
        'tipo',                 // 'percentual' ou 'valor_fixo'
        'montante',             // percentual ou valor do desconto
        'quantidade',           // usos totais disponiveis (null = ilimitado)
        'limite_por_usuario',   // usos por cliente (null = ilimitado)
        'usado',
        'data_inicio',
        'data_final',
        'valor_minimo',         // subtotal minimo do pedido para liberar o cupom
        'desconto_maximo',      // teto do desconto gerado
        'preco_maximo_produto', // so produtos ate esse preco recebem desconto
        'modelo',               // 'geral', 'categoria', 'marca', 'produto' ou 'nome'
        'categoria_id',
        'marca_id',
        'produto_id',
        'nome_termo',           // termo buscado no nome do produto
    ];

    protected $casts = [
        'ativo'                => 'boolean',
        'montante'             => 'float',
        'quantidade'           => 'integer',
        'limite_por_usuario'   => 'integer',
        'usado'                => 'integer',
        'valor_minimo'         => 'float',
        'desconto_maximo'      => 'float',
        'preco_maximo_produto' => 'float',
        'data_inicio'          => 'date',
        'data_final'           => 'date',
    ];

    // Relação com produto específico
    public function product()
    {
        return $this->belongsTo(Product::class, 'produto_id');
    }

    // Relação opcional com categoria
    public function category()
    {
        return $this->belongsTo(Category::class, 'categoria_id');
    }

    // Relação opcional com marca
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'marca_id');
    }

    public function usos()
    {
        return $this->hasMany(UserCupon::class, 'cupon_id');
    }

    /**
     * Cupons vigentes: ativos, dentro do período e com uso disponível.
     * As datas são comparadas por dia; senão o cupom expiraria às 00:00 do último dia.
     */
    public function scopeVigentes($query)
    {
        return $query->where('ativo', true)
            ->whereDate('data_inicio', '<=', now())
            ->whereDate('data_final', '>=', now())
            ->where(function ($q) {
                $q->whereNull('quantidade')->orWhereColumn('usado', '<', 'quantidade');
            });
    }

    // Mantido para compatibilidade com as chamadas antigas.
    public function scopeAtivos($query)
    {
        return $this->scopeVigentes($query);
    }

    public function estaVigente(): bool
    {
        if (!$this->ativo || !$this->data_inicio || !$this->data_final) {
            return false;
        }

        return $this->data_inicio->startOfDay() <= now()
            && $this->data_final->endOfDay() >= now()
            && $this->temUsoDisponivel();
    }

    public function temUsoDisponivel(): bool
    {
        return is_null($this->quantidade) || $this->usado < $this->quantidade;
    }

    // Compatibilidade com o código antigo.
    public function isAvailable(): bool
    {
        return $this->temUsoDisponivel();
    }

    public function usosRestantes(): ?int
    {
        return is_null($this->quantidade) ? null : max(0, $this->quantidade - $this->usado);
    }

    public function ehPercentual(): bool
    {
        return $this->tipo === 'percentual';
    }

    /**
     * O produto entra no escopo do cupom (categoria, marca, produto, nome ou geral)
     * e respeita o teto de preço por produto, quando definido.
     */
    public function aplicaAoProduto(Product $product): bool
    {
        if ($this->preco_maximo_produto && (float) $product->price > $this->preco_maximo_produto) {
            return false;
        }

        return match ($this->modelo) {
            'categoria' => (int) $product->category_id === (int) $this->categoria_id,
            'marca'     => (int) $product->brand_id === (int) $this->marca_id,
            'produto'   => (int) $product->id === (int) $this->produto_id,
            'nome'      => !empty($this->nome_termo) && str_contains(
                mb_strtolower((string) ($product->external_name ?? $product->name ?? '')),
                mb_strtolower($this->nome_termo)
            ),
            default     => true, // geral / null
        };
    }

    // Rótulo curto do desconto: "10%" ou "US$ 25,00".
    public function rotuloDesconto(): string
    {
        if ($this->ehPercentual()) {
            $valor = rtrim(rtrim(number_format($this->montante, 2, ',', '.'), '0'), ',');
            return $valor . '%';
        }

        return currency_format($this->montante);
    }

    // Descrição legível do escopo, usada no admin e no selo do produto.
    public function rotuloEscopo(): string
    {
        return match ($this->modelo) {
            'categoria' => __('messages.cupon_escopo_categoria', ['nome' => $this->category->name ?? '-']),
            'marca'     => __('messages.cupon_escopo_marca', ['nome' => $this->brand->name ?? '-']),
            'produto'   => __('messages.cupon_escopo_produto', ['nome' => $this->product->external_name ?? '-']),
            'nome'      => __('messages.cupon_escopo_nome', ['termo' => $this->nome_termo ?? '-']),
            default     => __('messages.cupon_escopo_geral'),
        };
    }
}
