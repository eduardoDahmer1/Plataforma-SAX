<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductReview extends Model
{
    use SoftDeletes;

    public const STATUS_APPROVED = 'approved';
    public const STATUS_HIDDEN = 'hidden';

    protected $fillable = [
        'product_id',
        'user_id',
        'author_name',
        'rating',
        'title',
        'comment',
        'status',
        'verified_purchase',
        'admin_note',
        'moderated_by',
        'moderated_at',
    ];

    protected $casts = [
        'rating' => 'integer',
        'verified_purchase' => 'boolean',
        'moderated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saved(function (ProductReview $review): void {
            static::refreshProductRating((int) $review->product_id);
        });

        static::deleted(function (ProductReview $review): void {
            static::refreshProductRating((int) $review->product_id);
        });

        static::restored(function (ProductReview $review): void {
            static::refreshProductRating((int) $review->product_id);
        });
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withoutGlobalScopes();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function moderator()
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public static function refreshProductRating(int $productId): void
    {
        if ($productId <= 0) {
            return;
        }

        $aggregate = static::query()
            ->approved()
            ->where('product_id', $productId)
            ->selectRaw('COUNT(*) AS total, COALESCE(AVG(rating), 0) AS average')
            ->first();

        Product::withoutGlobalScopes()
            ->whereKey($productId)
            ->update([
                'rating_count' => (int) ($aggregate?->total ?? 0),
                'rating_average' => round((float) ($aggregate?->average ?? 0), 2),
            ]);
    }
}
