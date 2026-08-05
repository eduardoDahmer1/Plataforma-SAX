<section class="sax-category-strip py-5">
    <div class="container-fluid px-lg-5">
        <div class="sax-category-swiper swiper">
            <div class="category-wrapper swiper-wrapper">
                @foreach($categories as $cat)
                    @php
                        $displayName = __("messages.{$cat->slug}");
                        if ($displayName === "messages.{$cat->slug}") {
                            $displayName = $cat->name;
                        }

                        $photoPath = ltrim($cat->photo, '/');

                        if (!empty($photoPath)) {
                            $imagePath = asset('storage/' . $photoPath);
                        } else {
                            $imagePath = "https://placehold.co/400x400/f2f2f2/a3a3a3?text=SAX";
                        }
                    @endphp

                    <a href="{{ url('categorias/' . $cat->slug) }}" class="category-item swiper-slide">
                        <div class="category-img-box">
                            <img src="{{ $imagePath }}"
                                 alt="{{ $displayName }}"
                                 loading="lazy"
                                 onerror="this.src='https://placehold.co/400x400/f2f2f2/a3a3a3?text={{ urlencode($displayName) }}'">
                        </div>
                        <span class="category-name">{{ $displayName }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</section>
