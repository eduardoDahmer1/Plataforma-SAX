<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductUpdateRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado a fazer esta requisição.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regras de validação para atualização de produto.
     */
    public function rules(): array
    {
        // Obtém o ID do produto sendo editado
        $productId = $this->route('product') ?? $this->route('id');
        
        return [
            // Campos obrigatórios
            'sku' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'sku')->ignore($productId)
            ],
            'price' => 'required|numeric|min:0|max:999999.99',
            'stock' => 'required|integer|min:0',
            
            // Campos opcionais de texto
            'name' => 'nullable|string|max:255',
            'external_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
            'size' => 'nullable|string|max:50',
            
            // Relacionamentos
            'brand_id' => 'nullable|exists:brands,id',
            'category_id' => 'nullable|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'childcategory_id' => 'nullable|exists:childcategories,id',
            
            // Imagens
            'photo' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:10240',
            'gallery' => 'nullable|array|max:10',
            'gallery.*' => 'image|mimes:jpeg,jpg,png,gif,webp|max:10240',
            
            // Destaques
            'highlights' => 'nullable|array',
            'highlights.*' => 'string',
            
            // Produto pai (array para múltiplos)
            'parent_id' => 'nullable|array',
            'parent_id.*' => [
                'exists:products,id',
                Rule::notIn([$productId]) // Produto não pode ser pai de si mesmo
            ],
            
            // Cores
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'color_parent_id' => 'nullable|array',
            'color_parent_id.*' => 'exists:products,id',
            'colors_values' => 'nullable|array',
            'colors_values.*' => 'string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
        ];
    }

    /**
     * Mensagens de validação personalizadas.
     */
    public function messages(): array
    {
        return [
            // SKU
            'sku.required' => 'O SKU é obrigatório.',
            'sku.unique' => 'Este SKU já está cadastrado em outro produto.',
            'sku.max' => 'O SKU não pode ter mais de 255 caracteres.',
            
            // Nome externo
            'external_name.max' => 'O nome externo não pode ter mais de 255 caracteres.',
            
            // Preço
            'price.required' => 'O preço é obrigatório.',
            'price.numeric' => 'O preço deve ser um número válido.',
            'price.min' => 'O preço deve ser maior ou igual a zero.',
            'price.max' => 'O preço não pode ser maior que R$ 999.999,99.',
            
            // Estoque
            'stock.required' => 'O estoque é obrigatório.',
            'stock.integer' => 'O estoque deve ser um número inteiro.',
            'stock.min' => 'O estoque não pode ser negativo.',
            
            // Descrição
            'description.max' => 'A descrição não pode ter mais de 5000 caracteres.',
            
            // Foto principal
            'photo.image' => 'O arquivo deve ser uma imagem.',
            'photo.mimes' => 'A foto deve ser nos formatos: jpeg, jpg, png, gif ou webp.',
            'photo.max' => 'A foto não pode ter mais de 10MB.',
            
            // Galeria
            'gallery.array' => 'A galeria deve ser um conjunto de imagens.',
            'gallery.max' => 'Você pode enviar no máximo 10 imagens para a galeria.',
            'gallery.*.image' => 'Todos os arquivos da galeria devem ser imagens.',
            'gallery.*.mimes' => 'As imagens da galeria devem ser nos formatos: jpeg, jpg, png, gif ou webp.',
            'gallery.*.max' => 'Cada imagem da galeria não pode ter mais de 10MB.',
            
            // Relacionamentos
            'brand_id.exists' => 'A marca selecionada não existe.',
            'category_id.exists' => 'A categoria selecionada não existe.',
            'subcategory_id.exists' => 'A subcategoria selecionada não existe.',
            'childcategory_id.exists' => 'A categoria filha selecionada não existe.',
            
            // Produto pai
            'parent_id.array' => 'Os produtos pai devem ser uma lista.',
            'parent_id.*.exists' => 'Um dos produtos pai selecionados não existe.',
            'parent_id.*.not_in' => 'Um produto não pode ser pai de si mesmo.',
            
            // Cores
            'color.regex' => 'A cor deve estar no formato hexadecimal (ex: #FF0000).',
            'color.max' => 'A cor deve ter exatamente 7 caracteres (#RRGGBB).',
            'color_parent_id.*.exists' => 'Um dos produtos de cor selecionados não existe.',
            'colors_values.*.regex' => 'Todas as cores devem estar no formato hexadecimal (ex: #FF0000).',
            'colors_values.*.max' => 'Cada cor deve ter exatamente 7 caracteres (#RRGGBB).',
            
            // Tamanho
            'size.max' => 'O tamanho não pode ter mais de 50 caracteres.',
            
            // Destaques
            'highlights.array' => 'Os destaques devem ser uma lista.',
        ];
    }

    /**
     * Prepara os dados antes da validação.
     */
    protected function prepareForValidation(): void
    {
        // Converte strings vazias em null para campos opcionais
        $this->merge([
            'brand_id' => $this->brand_id ?: null,
            'category_id' => $this->category_id ?: null,
            'subcategory_id' => $this->subcategory_id ?: null,
            'childcategory_id' => $this->childcategory_id ?: null,
            'name' => $this->name ?: null,
            'description' => $this->description ?: null,
            'size' => $this->size ?: null,
            'color' => $this->color ?: null,
        ]);

        // Se parent_id não for array, converte
        if ($this->has('parent_id') && !is_array($this->parent_id)) {
            $this->merge([
                'parent_id' => $this->parent_id ? [$this->parent_id] : []
            ]);
        }

        // Se color_parent_id não for array, converte
        if ($this->has('color_parent_id') && !is_array($this->color_parent_id)) {
            $this->merge([
                'color_parent_id' => $this->color_parent_id ? [$this->color_parent_id] : []
            ]);
        }
    }

    /**
     * Atributos personalizados para exibição nas mensagens de erro.
     */
    public function attributes(): array
    {
        return [
            'sku' => 'SKU',
            'external_name' => 'nome externo',
            'name' => 'nome',
            'description' => 'descrição',
            'price' => 'preço',
            'stock' => 'estoque',
            'brand_id' => 'marca',
            'category_id' => 'categoria',
            'subcategory_id' => 'subcategoria',
            'childcategory_id' => 'categoria filha',
            'photo' => 'foto principal',
            'gallery' => 'galeria',
            'highlights' => 'destaques',
            'parent_id' => 'produtos pai',
            'color' => 'cor',
            'color_parent_id' => 'cores relacionadas',
            'size' => 'tamanho',
        ];
    }

    /**
     * Validação adicional após as regras padrão.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $productId = $this->route('product') ?? $this->route('id');
            
            // Verifica se algum parent_id cria uma referência circular
            if ($this->has('parent_id') && is_array($this->parent_id)) {
                foreach ($this->parent_id as $parentId) {
                    if ($this->createsCircularReference($productId, $parentId)) {
                        $validator->errors()->add(
                            'parent_id',
                            'A seleção de produtos pai cria uma referência circular.'
                        );
                        break;
                    }
                }
            }
        });
    }

    /**
     * Verifica se criar este relacionamento geraria uma referência circular.
     */
    private function createsCircularReference($currentProductId, $parentId): bool
    {
        // Implementação básica - você pode expandir conforme necessário
        // Esta função deve verificar se o parentId tem currentProductId como seu pai
        
        $parent = \App\Models\Product::find($parentId);
        
        if (!$parent) {
            return false;
        }

        // Se o pai tem parent_id, verifica se é o produto atual
        if ($parent->parent_id) {
            $parentIds = is_string($parent->parent_id) 
                ? explode(',', $parent->parent_id) 
                : (array) $parent->parent_id;
            
            if (in_array($currentProductId, $parentIds)) {
                return true;
            }
        }

        return false;
    }
}