<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductService
{
    protected ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Sincroniza produtos filhos com o produto pai
     */
    public function syncChildProducts(Product $parent, array $newChildIds, array $oldChildIds): void
    {
        DB::transaction(function () use ($parent, $newChildIds, $oldChildIds) {
            // 1. Configura o produto atual como PAI (P)
            $parent->update([
                'product_role' => 'P',
                'parent_id' => null,
                'color_parent_id' => null
            ]);

            // 2. Identifica quem deve ser desvinculado
            $removedChildren = array_diff($oldChildIds, $newChildIds);
            foreach ($removedChildren as $childId) {
                $this->detachChild($childId);
            }

            // 3. Vincula os filhos e sincroniza a lista de IDs (conforme sua imagem do DB)
            // Criamos a string de IDs: "ID1,ID2,ID3..."
            $childListString = implode(',', $newChildIds);

            foreach ($newChildIds as $childId) {
                $this->attachChild($parent, $childId, $childListString);
            }
        });
    }

    /**
     * Desvincula um produto (volta a ser um produto independente)
     */
    protected function detachChild($childId): void
    {
        $child = Product::find($childId);
        if (!$child) return;

        // IMPORTANTE: Ao desvincular, NÃO deletamos a foto se ela for a mesma do pai,
        // apenas removemos a referência para o produto virar 'P' novamente.
        $child->update([
            'parent_id' => null,
            'product_role' => 'P',
            // Opcional: manter ou resetar foto/galeria dependendo da regra de negócio
        ]);
    }

    /**
     * Vincula o filho ao pai e herda atributos
     */
    protected function attachChild(Product $parent, int $childId, string $childListString): void
    {
        $child = Product::find($childId);
        if (!$child || $child->id === $parent->id) return;

        // Dados para herança
        $updateData = [
            'parent_id'    => $childListString, // Lista de irmãos conforme imagem
            'product_role' => 'F',
            'photo'        => $parent->photo,
            'gallery'      => $parent->gallery,
            'brand_id'     => $parent->brand_id,
            'category_id'  => $parent->category_id,
        ];

        // Herdar cor se o pai tiver
        if ($parent->color) {
            $updateData['color'] = $parent->color;
        }

        $child->update($updateData);
    }

    /**
     * Busca para o Autocomplete da View
     */
    public function searchProducts(string $query, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return Product::where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('external_name', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%");
            })
            ->limit($limit)
            ->get(['id', 'name', 'external_name', 'sku', 'photo']);
    }
}