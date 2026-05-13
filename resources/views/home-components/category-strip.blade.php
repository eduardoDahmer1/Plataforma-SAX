<section class="sax-category-strip py-5">
    <div class="container-fluid px-lg-5">
        <div class="category-wrapper">
            @foreach($categories as $cat)
                @php
                    $displayName = $cat->name;
                    if($cat->slug == 'feminino')  $displayName = __('messages.mulher');
                    if($cat->slug == 'masculino') $displayName = __('messages.homem');
                    if($cat->slug == 'infantil')  $displayName = __('messages.criancas');
                    if($cat->slug == 'optico')    $displayName = __('messages.lente');
                    if($cat->slug == 'casa')      $displayName = __('messages.casa');
                    $photoPath = ltrim($cat->photo, '/');
                    
                    if (!empty($photoPath)) {
                        $imagePath = asset('storage/' . $photoPath);
                    } else {
                        $imagePath = "https://placehold.co/400x400/f2f2f2/a3a3a3?text=SAX";
                    }
                @endphp

                <a href="{{ url('categorias/' . $cat->slug) }}" class="category-item">
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
</section>
