<?php

namespace Tests\Unit;

use App\Models\Product;
use PHPUnit\Framework\TestCase;

class ProductColorSwatchTest extends TestCase
{
    public function test_product_swatch_combines_all_registered_colors(): void
    {
        $product = new Product();
        $product->setRawAttributes([
            'color' => '#153E2C',
            'colors' => json_encode(['#A96337']),
        ]);

        $this->assertSame(['#153E2C', '#A96337'], $product->product_colors);
        $this->assertStringContainsString('linear-gradient', $product->color_swatch_style);
        $this->assertStringContainsString('#153E2C 0%', $product->color_swatch_style);
        $this->assertStringContainsString('#A96337 100%', $product->color_swatch_style);
    }

    public function test_single_color_keeps_a_solid_swatch(): void
    {
        $product = new Product();
        $product->setRawAttributes(['color' => '#111111', 'colors' => null]);

        $this->assertSame('background-color: #111111;', $product->color_swatch_style);
    }
}
