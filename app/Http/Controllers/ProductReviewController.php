<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductReviewController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validated($request);
        $user = $request->user();
        $reviewProduct = $this->reviewProduct($product);
        $review = ProductReview::withTrashed()
            ->where('product_id', $reviewProduct->id)
            ->where('user_id', $user->id)
            ->first();

        $attributes = [
            'author_name' => $this->publicAuthorName((string) $user->name),
            'rating' => $data['rating'],
            'title' => $data['title'] ?? null,
            'comment' => $data['comment'] ?? null,
            'status' => ProductReview::STATUS_APPROVED,
            'verified_purchase' => $this->hasPurchased($user->id, $reviewProduct),
            'admin_note' => null,
            'moderated_by' => null,
            'moderated_at' => null,
        ];

        if ($review) {
            $review->fill($attributes);
            $review->save();
            if ($review->trashed()) {
                $review->restore();
            }
            $message = __('messages.review_success_updated');
        } else {
            ProductReview::create($attributes + [
                'product_id' => $reviewProduct->id,
                'user_id' => $user->id,
            ]);
            $message = __('messages.review_success_created');
        }

        return redirect($this->productUrl($reviewProduct))->with('success', $message);
    }

    public function update(Request $request, ProductReview $review): RedirectResponse
    {
        abort_unless((int) $review->user_id === (int) $request->user()->id, 403);
        $data = $this->validated($request);
        $product = $review->product;

        $review->update([
            'rating' => $data['rating'],
            'title' => $data['title'] ?? null,
            'comment' => $data['comment'] ?? null,
            'status' => ProductReview::STATUS_APPROVED,
            'verified_purchase' => $this->hasPurchased((int) $request->user()->id, $product),
            'admin_note' => null,
            'moderated_by' => null,
            'moderated_at' => null,
        ]);

        return redirect($this->productUrl($product))->with('success', __('messages.review_success_updated'));
    }

    public function destroy(Request $request, ProductReview $review): RedirectResponse
    {
        abort_unless((int) $review->user_id === (int) $request->user()->id, 403);
        $product = $review->product;
        $review->delete();

        return redirect($this->productUrl($product))->with('success', __('messages.review_success_deleted'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['nullable', 'string', 'max:120'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function reviewProduct(Product $product): Product
    {
        return Product::withoutGlobalScopes()->findOrFail($product->reviewAnchorId());
    }

    private function hasPurchased(int $userId, Product $product): bool
    {
        $familyIds = Product::withoutGlobalScopes()
            ->where(fn ($query) => $query->whereKey($product->id)->orWhere('parent_id', $product->id))
            ->pluck('id');

        return Order::query()
            ->where('user_id', $userId)
            ->where(fn ($query) => $query->where('status', 'paid')->orWhere('payment_status', 'paid'))
            ->whereHas('items', fn ($query) => $query->whereIn('product_id', $familyIds))
            ->exists();
    }

    private function publicAuthorName(string $name): string
    {
        $parts = collect(preg_split('/\s+/', trim($name)) ?: [])->filter()->values();
        if ($parts->isEmpty()) {
            return __('messages.review_customer_label');
        }

        $first = Str::title((string) $parts->first());
        $last = $parts->count() > 1 ? ' ' . Str::upper(Str::substr((string) $parts->last(), 0, 1)) . '.' : '';

        return $first . $last;
    }

    private function productUrl(Product $product): string
    {
        return route('produto.show', $product->slug ?: $product->id);
    }
}
