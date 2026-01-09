<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado a fazer esta requisição.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regras de validação para criação de produto.
     */
    public function rules(): array
    {
        return [
            // Campos obrigatórios
            'sku' => 'required|string|max:255|unique:products,sku',
            'external_name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0|max:999999.99',
            'stock' => 'required|integer|min:0',
            
            // Campos opcionais de texto
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
            'size' => 'nullable|string|max:50',
            
            // Relacionamentos
            'brand_id' => 'nullable|exists:brands,id',
            'category_id' => 'nullable|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'childcategory_id' => 'nullable|exists:childcategories,id',
            'parent_id' => 'nullable|exists:products,id',
            
            // Imagens
            'photo' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:10240',
            'gallery' => 'nullable|array|max:10',
            'gallery.*' => 'image|mimes:jpeg,jpg,png,gif,webp|max:10240',
            
            // Destaques
            'highlights' => 'nullable|array',
            'highlights.*' => 'string',
            
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
            'sku.unique' => 'Este SKU já está cadastrado no sistema.',
            'sku.max' => 'O SKU não pode ter mais de 255 caracteres.',
            
            // Nome externo
            'external_name.required' => 'O nome externo é obrigatório.',
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
            'parent_id.exists' => 'O produto pai selecionado não existe.',
            
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
            'parent_id' => $this->parent_id ?: null,
            'name' => $this->name ?: null,
            'description' => $this->description ?: null,
            'size' => $this->size ?: null,
            'color' => $this->color ?: null,
        ]);
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
            'parent_id' => 'produto pai',
            'color' => 'cor',
            'color_parent_id' => 'cores relacionadas',
            'size' => 'tamanho',
        ];
    }
}