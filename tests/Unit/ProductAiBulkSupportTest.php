<?php

namespace Tests\Unit;

use App\Http\Controllers\Admin\ProductAiBatchController;
use App\Services\OpenAICatalogService;
use App\Services\ProductAiProposalApplier;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

class ProductAiBulkSupportTest extends TestCase
{
    public function test_excel_numeric_codes_are_normalized_without_decimal_suffix(): void
    {
        $method = new ReflectionMethod(ProductAiBatchController::class, 'normalizeCellValue');
        $controller = (new ReflectionClass(ProductAiBatchController::class))->newInstanceWithoutConstructor();

        $this->assertSame('12345', $method->invoke($controller, 12345.0));
        $this->assertSame('REF-001', $method->invoke($controller, ' REF-001 '));
    }

    public function test_descriptions_are_saved_as_safe_paragraph_html(): void
    {
        $method = new ReflectionMethod(ProductAiProposalApplier::class, 'descriptionToHtml');
        $html = $method->invoke(new ProductAiProposalApplier, "Primer párrafo.\n\nSegundo <b>párrafo</b>.");

        $this->assertSame('<p>Primer párrafo.</p><p>Segundo &lt;b&gt;párrafo&lt;/b&gt;.</p>', $html);
    }

    public function test_only_explicit_hex_colors_are_applied(): void
    {
        $method = new ReflectionMethod(ProductAiProposalApplier::class, 'verifiedHexColor');
        $applier = new ProductAiProposalApplier;

        $this->assertSame('#AABBCC', $method->invoke($applier, [
            'verified_attributes' => ['Color' => '#aabbcc'],
        ]));
        $this->assertNull($method->invoke($applier, [
            'verified_attributes' => ['Color' => 'Azul marino'],
        ]));
    }

    public function test_bulk_proposal_requires_complete_content_in_every_language(): void
    {
        $method = new ReflectionMethod(ProductAiProposalApplier::class, 'assertValidProposal');

        $this->expectException(\RuntimeException::class);
        $method->invoke(new ProductAiProposalApplier, [
            'commercial_name' => ['pt_br' => 'PRODUTO', 'es' => '', 'en' => 'PRODUCT'],
            'descriptions' => ['pt_br' => 'Texto', 'es' => 'Texto', 'en' => 'Text'],
        ]);
    }

    public function test_bulk_proposal_accepts_short_complete_descriptions(): void
    {
        $method = new ReflectionMethod(ProductAiProposalApplier::class, 'assertValidProposal');

        $result = $method->invoke(new ProductAiProposalApplier, [
            'commercial_name' => [
                'pt_br' => 'PRODUTO',
                'es' => 'PRODUCTO',
                'en' => 'PRODUCT',
            ],
            'descriptions' => [
                'pt_br' => 'Descrição curta baseada somente em características verificadas.',
                'es' => 'Descripción corta basada únicamente en características verificadas.',
                'en' => 'Short description based only on verified product characteristics.',
            ],
        ]);

        $this->assertNull($result);
    }

    public function test_ai_taxonomy_requires_a_consistent_category_chain(): void
    {
        $method = new ReflectionMethod(OpenAICatalogService::class, 'normalizeProposal');
        $proposal = [
            'commercial_name' => ['pt_br' => 'Produto', 'es' => 'Producto', 'en' => 'Product'],
            'verified_attributes' => [],
            'taxonomy_selection' => [
                'category_id' => 10,
                'subcategory_id' => 20,
                'childcategory_id' => 30,
            ],
        ];
        $categories = collect([(object) ['id' => 10, 'name' => 'Calçados', 'slug' => 'calcados']])->keyBy('id');
        $subcategories = collect([(object) ['id' => 20, 'category_id' => 10, 'name' => 'Tênis', 'slug' => 'tenis']])->keyBy('id');
        $childCategories = collect([(object) ['id' => 30, 'category_id' => 10, 'subcategory_id' => 20, 'name' => 'Casuais', 'slug' => 'casuais']])->keyBy('id');

        $normalized = $method->invoke(new OpenAICatalogService, $proposal, $categories, $subcategories, $childCategories);

        $this->assertSame(10, $normalized['taxonomy_selection']['category_id']);
        $this->assertSame(20, $normalized['taxonomy_selection']['subcategory_id']);
        $this->assertSame(30, $normalized['taxonomy_selection']['childcategory_id']);

        $proposal['taxonomy_selection']['category_id'] = 999;
        $normalized = $method->invoke(new OpenAICatalogService, $proposal, $categories, $subcategories, $childCategories);

        $this->assertNull($normalized['taxonomy_selection']['category_id']);
        $this->assertNull($normalized['taxonomy_selection']['subcategory_id']);
        $this->assertNull($normalized['taxonomy_selection']['childcategory_id']);
    }
}
