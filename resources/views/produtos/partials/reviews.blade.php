@php
    $ratingCount = (int) ($reviewProduct->rating_count ?? 0);
    $ratingAverage = (float) ($reviewProduct->rating_average ?? 0);
    $selectedRating = (int) old('rating', $userReview?->rating ?? 0);
@endphp

<section class="product-reviews-section" id="avaliacoes" aria-labelledby="product-reviews-title">
    <div class="container-fluid px-3 px-lg-5">
        <x-alert type="success" :message="session('success')" />
        <x-alert type="warning" :message="session('warning')" />

        <div class="product-reviews-heading">
            <div>
                <span>{{ __('messages.review_average_label') }}</span>
                <h2 id="product-reviews-title">{{ __('messages.product_reviews') }}</h2>
            </div>
            @if($ratingCount > 0)
                <a href="#review-list">{{ __('messages.review_count_summary', ['count' => number_format($ratingCount, 0, ',', '.')]) }}</a>
            @endif
        </div>

        <div class="product-reviews-overview">
            <div class="product-rating-summary">
                <strong>{{ $ratingCount ? number_format($ratingAverage, 1, ',', '.') : '—' }}</strong>
                <div>
                    <x-rating-stars :rating="$ratingAverage" />
                    <span>{{ $ratingCount ? number_format($ratingCount, 0, ',', '.') . ' ' . ($ratingCount === 1 ? __('messages.product_review_singular') : __('messages.product_reviews_plural')) : __('messages.review_no_reviews') }}</span>
                </div>
            </div>

            <div class="product-rating-breakdown" aria-label="{{ __('messages.review_distribution_label') }}">
                @for($rating = 5; $rating >= 1; $rating--)
                    @php
                        $amount = (int) ($ratingBreakdown[$rating] ?? 0);
                        $percentage = $ratingCount > 0 ? round(($amount / $ratingCount) * 100, 1) : 0;
                    @endphp
                    <div class="product-rating-breakdown__row">
                        <span>{{ $rating }} <i class="fa-solid fa-star" aria-hidden="true"></i></span>
                        <div><span style="width: {{ $percentage }}%"></span></div>
                        <small>{{ $amount }}</small>
                    </div>
                @endfor
            </div>

            <div class="product-review-compose">
                @auth
                    @if(auth()->user()->canShop())
                        <div class="product-review-compose__header">
                            <div>
                                <span>{{ $userReview ? __('messages.review_edit') : __('messages.review_write') }}</span>
                                <h3>{{ $userReview ? __('messages.review_update') : __('messages.review_submit') }}</h3>
                            </div>
                            @if($userReview?->verified_purchase)
                                <span class="product-review-verified"><i class="fa-solid fa-circle-check"></i> {{ __('messages.review_verified_purchase') }}</span>
                            @endif
                        </div>

                        @if($userReview && $userReview->status === \App\Models\ProductReview::STATUS_HIDDEN)
                            <p class="product-review-moderation-note">{{ __('messages.review_hidden_notice') }}</p>
                        @endif

                        <form method="POST" action="{{ $userReview ? route('product-reviews.update', $userReview) : route('product-reviews.store', $reviewProduct) }}" class="product-review-form">
                            @csrf
                            @if($userReview) @method('PUT') @endif

                            <fieldset>
                                <legend>{{ __('messages.review_rating') }}</legend>
                                <div class="product-review-rating-input">
                                    @for($rating = 5; $rating >= 1; $rating--)
                                        <input type="radio" name="rating" id="review-rating-{{ $rating }}" value="{{ $rating }}" @checked($selectedRating === $rating) required>
                                        <label for="review-rating-{{ $rating }}" aria-label="{{ $rating }} de 5"><i class="fa-solid fa-star"></i></label>
                                    @endfor
                                </div>
                                @error('rating')<small class="text-danger">{{ $message }}</small>@enderror
                            </fieldset>

                            <label>
                                <span>{{ __('messages.review_title_label') }}</span>
                                <input type="text" name="title" maxlength="120" value="{{ old('title', $userReview?->title) }}" placeholder="{{ __('messages.review_title_placeholder') }}">
                                @error('title')<small class="text-danger">{{ $message }}</small>@enderror
                            </label>

                            <label>
                                <span>{{ __('messages.review_comment_label') }}</span>
                                <textarea name="comment" rows="4" maxlength="2000" placeholder="{{ __('messages.review_comment_placeholder') }}">{{ old('comment', $userReview?->comment) }}</textarea>
                                @error('comment')<small class="text-danger">{{ $message }}</small>@enderror
                            </label>

                            <div class="product-review-form__actions">
                                <button type="submit">{{ $userReview ? __('messages.review_update') : __('messages.review_submit') }}</button>
                            </div>
                        </form>

                        @if($userReview)
                            <form method="POST" action="{{ route('product-reviews.destroy', $userReview) }}" class="product-review-delete-form"
                                  onsubmit="return confirm(@js(__('messages.review_delete_confirm')))">
                                @csrf
                                @method('DELETE')
                                <button type="submit"><i class="fa-regular fa-trash-can"></i> {{ __('messages.review_delete') }}</button>
                            </form>
                        @endif
                    @endif
                @else
                    <div class="product-review-login">
                        <i class="fa-regular fa-user"></i>
                        <div>
                            <strong>{{ __('messages.review_write') }}</strong>
                            <span>{{ __('messages.review_login_message') }}</span>
                        </div>
                        <a href="{{ route('login', ['redirect' => url()->current() . '#avaliacoes']) }}" class="js-requires-login" data-redirect-to="{{ url()->current() }}#avaliacoes">{{ __('messages.review_login_action') }}</a>
                    </div>
                @endauth
            </div>
        </div>

        <div class="product-review-list" id="review-list">
            @forelse($reviews as $review)
                <article class="product-review-item">
                    <header>
                        <div class="product-review-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($review->author_name ?: __('messages.review_customer_label'), 0, 1)) }}</div>
                        <div>
                            <strong>{{ $review->author_name ?: __('messages.review_customer_label') }}</strong>
                            <time datetime="{{ $review->created_at->toDateString() }}">{{ $review->created_at->format('d/m/Y') }}</time>
                        </div>
                        @if($review->verified_purchase)
                            <span class="product-review-verified"><i class="fa-solid fa-circle-check"></i> {{ __('messages.review_verified_purchase') }}</span>
                        @endif
                    </header>
                    <x-rating-stars :rating="$review->rating" compact class="mb-2" />
                    @if(filled($review->title))<h3>{{ $review->title }}</h3>@endif
                    @if(filled($review->comment))<p>{{ $review->comment }}</p>@endif
                </article>
            @empty
                <div class="product-reviews-empty">
                    <i class="fa-regular fa-star"></i>
                    <p>{{ __('messages.review_no_reviews') }}</p>
                </div>
            @endforelse
        </div>

        @if($reviews->hasPages())
            <div class="product-review-pagination">{{ $reviews->onEachSide(1)->fragment('avaliacoes')->links() }}</div>
        @endif
    </div>
</section>
