<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductAiPreparation;
use App\Models\ProductTranslation;
use Illuminate\Support\Facades\DB;

class ProductAiProposalApplier
{
    public function apply(Product $product, array $result, ?int $userId = null, array $targetProductIds = []): void
    {
        DB::transaction(function () use ($product, $result, $userId, $targetProductIds) {
            $product = Product::withoutGlobalScopes()->lockForUpdate()->findOrFail($product->id);
            $proposal = (array) data_get($result, 'proposal', []);
            $this->assertValidProposal($proposal);
            $generatedAt = now()->setMicrosecond(0);
            $translations = [
                'pt-br' => [
                    'name' => data_get($proposal, 'commercial_name.pt_br'),
                    'details' => $this->descriptionToHtml(data_get($proposal, 'descriptions.pt_br')),
                ],
                'es' => [
                    'name' => data_get($proposal, 'commercial_name.es'),
                    'details' => $this->descriptionToHtml(data_get($proposal, 'descriptions.es')),
                ],
                'en' => [
                    'name' => data_get($proposal, 'commercial_name.en'),
                    'details' => $this->descriptionToHtml(data_get($proposal, 'descriptions.en')),
                ],
            ];

            $suggestedSubcategoryId = data_get($proposal, 'taxonomy_selection.subcategory_id');
            $suggestedChildCategoryId = data_get($proposal, 'taxonomy_selection.childcategory_id');
            $data = [
                'name' => $translations['pt-br']['name'],
                'description' => $translations['pt-br']['details'],
                'subcategory_id' => $suggestedSubcategoryId ?: $product->subcategory_id,
                'childcategory_id' => $suggestedSubcategoryId
                    ? ($suggestedChildCategoryId ?: null)
                    : $product->childcategory_id,
                'updated_by' => $userId,
                'admin_edited_at' => now(),
            ];

            if ($color = $this->verifiedHexColor($proposal)) {
                $data['color'] = $color;
                if (Product::supportsMultipleColors()) {
                    $data['colors'] = [$color];
                }
            }

            $product->update($data);
            $this->saveTranslations($product->id, $translations);

            $resolvedTargetIds = collect($targetProductIds)
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->push((int) $product->id)
                ->unique()
                ->values();
            if ($targetProductIds === []) {
                $resolvedTargetIds = $resolvedTargetIds
                    ->merge(Product::withoutGlobalScopes()->where('parent_id', $product->id)->pluck('id'))
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values();
            }
            $otherProducts = Product::withoutGlobalScopes()
                ->whereIn('id', $resolvedTargetIds->reject(fn ($id) => $id === (int) $product->id))
                ->lockForUpdate()
                ->get();

            foreach ($otherProducts as $targetProduct) {
                $targetData = [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'brand_id' => $product->brand_id,
                    'category_id' => $product->category_id,
                    'subcategory_id' => $data['subcategory_id'],
                    'childcategory_id' => $data['childcategory_id'],
                    'updated_by' => $userId,
                    'admin_edited_at' => now(),
                ];
                if (isset($data['color'])) {
                    $targetData['color'] = $data['color'];
                    if (Product::supportsMultipleColors()) {
                        $targetData['colors'] = $data['colors'];
                    }
                }
                $targetProduct->update($targetData);
                $this->saveTranslations($targetProduct->id, $translations);
            }

            $preparationData = [
                'status' => ProductAiPreparation::STATUS_COMPLETED,
                'sources' => array_slice((array) data_get($result, 'research.sources', []), 0, 3),
                'confidence' => data_get($proposal, 'confidence'),
                'model' => data_get($result, 'source.model'),
                'error_message' => null,
                'generated_at' => $generatedAt,
                'completed_at' => $generatedAt,
            ];
            foreach ($resolvedTargetIds as $targetProductId) {
                ProductAiPreparation::updateOrCreate(['product_id' => $targetProductId], $preparationData);
            }
        });
    }

    private function saveTranslations(int $productId, array $translations): void
    {
        foreach ($translations as $locale => $translation) {
            ProductTranslation::updateOrCreate(
                ['product_id' => $productId, 'locale' => $locale],
                $translation,
            );
        }
    }

    private function descriptionToHtml($description): string
    {
        $paragraphs = preg_split('/\R{2,}/u', trim((string) $description), -1, PREG_SPLIT_NO_EMPTY);

        return collect($paragraphs)->map(function ($paragraph) {
            return '<p>'.nl2br(e(trim($paragraph)), false).'</p>';
        })->implode('');
    }

    private function verifiedHexColor(array $proposal): ?string
    {
        $attributes = array_merge(
            (array) data_get($proposal, 'verified_attributes', []),
            (array) data_get($proposal, 'suggested_attributes', []),
        );
        foreach ($attributes as $name => $attribute) {
            $attributeName = is_array($attribute) ? ($attribute['name'] ?? $name) : $name;
            $value = is_array($attribute) ? ($attribute['value'] ?? null) : $attribute;
            if (! preg_match('/color|cor/i', (string) $attributeName)) {
                continue;
            }
            $color = strtoupper(trim((string) $value));
            if (preg_match('/^#[0-9A-F]{6}$/', $color)) {
                return $color;
            }
        }

        return null;
    }

    private function assertValidProposal(array $proposal): void
    {
        foreach (['pt_br', 'es', 'en'] as $locale) {
            $name = trim((string) data_get($proposal, "commercial_name.{$locale}"));
            $description = trim((string) data_get($proposal, "descriptions.{$locale}"));
            if ($name === '' || mb_strlen($name) > 255 || $description === '') {
                throw new \RuntimeException('La IA devolvió contenido incompleto para uno o más idiomas.');
            }
        }
    }
}
