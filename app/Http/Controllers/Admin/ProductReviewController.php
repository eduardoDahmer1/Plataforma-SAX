<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'product_id' => ['nullable', 'integer', 'min:1'],
            'rating' => ['nullable', 'integer', 'between:1,5'],
            'status' => ['nullable', 'in:approved,hidden'],
            'verified' => ['nullable', 'in:0,1'],
            'comment' => ['nullable', 'in:with,without'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            'sort' => ['nullable', 'in:latest,oldest,rating_high,rating_low'],
        ]);

        $query = ProductReview::query()
            ->with([
                'product:id,name,external_name,sku,slug,photo,rating_average,rating_count',
                'user:id,name,email',
                'moderator:id,name',
            ])
            ->when(filled($filters['search'] ?? null), function ($query) use ($filters): void {
                $term = trim((string) $filters['search']);
                $query->where(function ($scope) use ($term): void {
                    $scope->where('author_name', 'like', "%{$term}%")
                        ->orWhere('title', 'like', "%{$term}%")
                        ->orWhere('comment', 'like', "%{$term}%")
                        ->orWhereHas('user', fn ($user) => $user
                            ->where('name', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%"))
                        ->orWhereHas('product', fn ($product) => $product
                            ->where('name', 'like', "%{$term}%")
                            ->orWhere('external_name', 'like', "%{$term}%")
                            ->orWhere('sku', 'like', "%{$term}%"));
                });
            })
            ->when(filled($filters['product_id'] ?? null), fn ($query) => $query->where('product_id', (int) $filters['product_id']))
            ->when(filled($filters['rating'] ?? null), fn ($query) => $query->where('rating', (int) $filters['rating']))
            ->when(filled($filters['status'] ?? null), fn ($query) => $query->where('status', $filters['status']))
            ->when(array_key_exists('verified', $filters) && $filters['verified'] !== null, fn ($query) => $query->where('verified_purchase', (bool) $filters['verified']))
            ->when(($filters['comment'] ?? null) === 'with', fn ($query) => $query->whereNotNull('comment')->where('comment', '<>', ''))
            ->when(($filters['comment'] ?? null) === 'without', fn ($query) => $query->where(fn ($scope) => $scope->whereNull('comment')->orWhere('comment', '')))
            ->when(filled($filters['date_from'] ?? null), fn ($query) => $query->whereDate('created_at', '>=', $filters['date_from']))
            ->when(filled($filters['date_to'] ?? null), fn ($query) => $query->whereDate('created_at', '<=', $filters['date_to']));

        match ($filters['sort'] ?? 'latest') {
            'oldest' => $query->oldest(),
            'rating_high' => $query->orderByDesc('rating')->latest('id'),
            'rating_low' => $query->orderBy('rating')->latest('id'),
            default => $query->latest(),
        };

        $reviews = $query->paginate(24)->withQueryString();
        $all = ProductReview::query();
        $approvedCount = (clone $all)->approved()->count();
        $stats = [
            'total' => (clone $all)->count(),
            'products' => (clone $all)->distinct()->count('product_id'),
            'approved' => $approvedCount,
            'hidden' => (clone $all)->where('status', ProductReview::STATUS_HIDDEN)->count(),
            'verified' => (clone $all)->where('verified_purchase', true)->count(),
            'comments' => (clone $all)->whereNotNull('comment')->where('comment', '<>', '')->count(),
            'recent' => (clone $all)->where('created_at', '>=', now()->subDays(30))->count(),
            'average' => round((float) ((clone $all)->approved()->avg('rating') ?? 0), 2),
        ];

        $ratingCounts = (clone $all)->approved()
            ->selectRaw('rating, COUNT(*) AS total')
            ->groupBy('rating')
            ->pluck('total', 'rating');
        $ratingDistribution = collect(range(5, 1))->map(fn ($rating) => (object) [
            'rating' => $rating,
            'total' => (int) ($ratingCounts[$rating] ?? 0),
            'percentage' => $approvedCount > 0 ? round(((int) ($ratingCounts[$rating] ?? 0) / $approvedCount) * 100, 1) : 0,
        ]);

        $productSummaryQuery = fn () => ProductReview::query()
            ->approved()
            ->with('product:id,name,external_name,sku,slug,photo')
            ->selectRaw('product_id, COUNT(*) AS reviews_count, AVG(rating) AS reviews_average, SUM(verified_purchase = 1) AS verified_count')
            ->groupBy('product_id');

        $mostReviewedProducts = $productSummaryQuery()
            ->orderByDesc('reviews_count')
            ->orderByDesc('reviews_average')
            ->limit(8)
            ->get();
        $attentionProducts = $productSummaryQuery()
            ->havingRaw('AVG(rating) <= 3.5')
            ->orderBy('reviews_average')
            ->orderByDesc('reviews_count')
            ->limit(6)
            ->get();

        $reviewedProductIds = ProductReview::query()->distinct()->pluck('product_id');
        $reviewedProducts = Product::withoutGlobalScopes()
            ->whereIn('id', $reviewedProductIds)
            ->orderByRaw('COALESCE(NULLIF(external_name, ""), name)')
            ->get(['id', 'name', 'external_name', 'sku']);

        return view('admin.product-reviews.index', compact(
            'reviews', 'stats', 'ratingDistribution', 'mostReviewedProducts', 'attentionProducts', 'reviewedProducts'
        ));
    }

    public function update(Request $request, ProductReview $review): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:' . ProductReview::STATUS_APPROVED . ',' . ProductReview::STATUS_HIDDEN],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $review->update([
            'status' => $data['status'],
            'admin_note' => $data['admin_note'] ?? null,
            'moderated_by' => $request->user()->id,
            'moderated_at' => now(),
        ]);

        return back()->with('success', $data['status'] === ProductReview::STATUS_APPROVED
            ? 'Avaliação publicada novamente.'
            : 'Avaliação ocultada da loja.');
    }

    public function destroy(ProductReview $review): RedirectResponse
    {
        $review->delete();

        return back()->with('success', 'Avaliação removida com sucesso.');
    }
}
