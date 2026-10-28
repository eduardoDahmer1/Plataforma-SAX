<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductTranslation;
use App\Services\Dhl\DhlCustomsProductData;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class DhlCustomsProductDataTest extends TestCase
{
    public function test_it_uses_clean_english_catalog_content_for_customs(): void
    {
        $product = new Product(['shipping_country_of_origin' => null]);
        $product->setRelation('translations', new Collection([
            new ProductTranslation([
                'locale' => 'en',
                'details' => '<p>Eau de parfum&nbsp;100 ml <strong>gift set</strong></p>',
            ]),
        ]));

        $service = new DhlCustomsProductData;

        $this->assertSame('Eau de parfum 100 ml gift set', $service->englishDescription($product));
        $this->assertSame('PY', $service->countryOfOrigin($product, 'py'));
    }

    public function test_product_origin_has_priority_over_the_store_fallback(): void
    {
        $product = new Product(['shipping_country_of_origin' => 'fr']);
        $product->setRelation('translations', new Collection);

        $service = new DhlCustomsProductData;

        $this->assertSame('FR', $service->countryOfOrigin($product, 'PY'));
        $this->assertNull($service->englishDescription($product));
    }
}
