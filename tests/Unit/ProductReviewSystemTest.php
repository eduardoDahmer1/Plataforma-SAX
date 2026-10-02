<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ProductReviewSystemTest extends TestCase
{
    public function test_customer_review_routes_require_the_client_middleware(): void
    {
        $routes = file_get_contents(__DIR__ . '/../../routes/web.php');
        $controller = file_get_contents(__DIR__ . '/../../app/Http/Controllers/ProductReviewController.php');
        $provider = file_get_contents(__DIR__ . '/../../app/Providers/RouteServiceProvider.php');

        $this->assertStringContainsString("middleware(['cliente', 'throttle:product-reviews'])", $routes);
        $this->assertStringContainsString("Route::get('/produto/{id_or_slug}/avaliacoes'", $routes);
        $this->assertStringContainsString("RateLimiter::for('product-reviews'", $provider);
        $this->assertStringNotContainsString(".'#avaliacoes'", $provider);
        $this->assertStringNotContainsString(".'#avaliacoes'", $controller);
        $this->assertStringContainsString('abort_unless((int) $review->user_id === (int) $request->user()->id, 403)', $controller);
        $this->assertStringContainsString("'verified_purchase' => \$this->hasPurchased", $controller);
    }
    public function test_storefront_exposes_ratings_only_when_the_product_has_reviews(): void
    {
        $card = file_get_contents(__DIR__ . '/../../resources/views/components/product-card.blade.php');
        $product = file_get_contents(__DIR__ . '/../../resources/views/produtos/show.blade.php');
        $reviews = file_get_contents(__DIR__ . '/../../resources/views/produtos/partials/reviews.blade.php');

        $this->assertStringContainsString("rating_count ?? 0) > 0", $card);
        $this->assertStringContainsString("@include('produtos.partials.reviews')", $product);
        $this->assertStringContainsString("auth()->user()->canShop()", $reviews);
        $this->assertStringContainsString('review_verified_purchase', $reviews);
        $this->assertStringContainsString("fragment('avaliacoes')", $reviews);
    }

    public function test_admin_products_page_links_to_review_moderation(): void
    {
        $products = file_get_contents(__DIR__ . '/../../resources/views/admin/products/index.blade.php');
        $admin = file_get_contents(__DIR__ . '/../../resources/views/admin/product-reviews/index.blade.php');
        $model = file_get_contents(__DIR__ . '/../../app/Models/ProductReview.php');
        $adminController = file_get_contents(__DIR__ . '/../../app/Http/Controllers/Admin/ProductReviewController.php');

        $this->assertStringContainsString("route('admin.products.ratings.index')", $products);
        $this->assertStringContainsString('admin-review-filters', $admin);
        $this->assertStringContainsString('Compra verificada', $admin);
        $this->assertStringContainsString('Produtos avaliados', $admin);
        $this->assertStringContainsString('Distribuição das notas', $admin);
        $this->assertStringContainsString('Produtos mais avaliados', $admin);
        $this->assertStringContainsString('Produtos que pedem atenção', $admin);
        $this->assertStringContainsString('name="product_id"', $admin);
        $this->assertStringContainsString('name="comment"', $admin);
        $this->assertStringContainsString('name="date_from"', $admin);
        $this->assertStringContainsString("'products' => (clone \$all)->distinct()->count('product_id')", $adminController);
        $this->assertStringContainsString('ratingDistribution', $adminController);
        $this->assertStringContainsString('refreshProductRating', $model);
        $this->assertStringContainsString("->approved()", $model);
    }
}
