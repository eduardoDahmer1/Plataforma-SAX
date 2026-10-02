@props(['rating' => 0, 'count' => null, 'compact' => false])

@php($numericRating = max(0, min(5, (float) $rating)))

<span {{ $attributes->class(['sax-rating-stars', 'is-compact' => $compact]) }}
      role="img" aria-label="{{ number_format($numericRating, 1, ',', '.') }} de 5">
    @for($star = 1; $star <= 5; $star++)
        @if($numericRating >= $star - .25)
            <i class="fa-solid fa-star" aria-hidden="true"></i>
        @elseif($numericRating >= $star - .75)
            <i class="fa-solid fa-star-half-stroke" aria-hidden="true"></i>
        @else
            <i class="fa-regular fa-star" aria-hidden="true"></i>
        @endif
    @endfor
</span>
