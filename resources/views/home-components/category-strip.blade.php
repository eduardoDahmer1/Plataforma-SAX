<section class="sax-category-strip py-5">
    <div class="container-fluid px-lg-5">
        <div class="category-wrapper">
            @foreach($categories as $cat)
                @php
                    // 1. Mapeamento de Tradução Visual (De Slugs do Banco para Nomes SAX)
                    $displayName = $cat->name;
                    if($cat->slug == 'feminino')  $displayName = 'MUJER';
                    if($cat->slug == 'masculino') $displayName = 'HOMBRE';
                    if($cat->slug == 'infantil')  $displayName = 'NIÑOS';
                    if($cat->slug == 'optico')    $displayName = 'LENTES';
                    if($cat->slug == 'casa')      $displayName = 'HOGAR';

                    $photoPath = ltrim($cat->photo, '/');
                    
                    // Se a foto existir no banco, montamos a URL via storage
                    if (!empty($photoPath)) {
                        $imagePath = asset('storage/' . $photoPath);
                    } else {
                        // Placeholder se o campo estiver vazio ou 0
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

<style>
    /* Estilos preservados conforme solicitado */
    .sax-category-strip { background-color: #fff; padding-bottom: 2rem !important; }

    .category-wrapper {
        display: flex;
        justify-content: center;
        gap: 20px;
        flex-wrap: nowrap;
        overflow-x: auto;
    }

    .category-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-decoration: none !important;
        flex: 0 0 18%; 
        max-width: 220px;
    }

    .category-img-box {
        width: 100%;
        aspect-ratio: 1 / 1;
        background-color: #f2f2f2;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 12px;
        transition: all 0.3s ease;
    }

    .category-item:hover .category-img-box {
        background-color: #e9e9e9;
        transform: translateY(-5px);
    }

    .category-img-box img {
        width: 80%;
        height: 80%;
        object-fit: contain;
    }

    .category-name {
        font-size: 0.8rem;
        font-weight: 700;
        color: #000;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        text-align: center;
        margin-top: 5px;
    }

    .category-wrapper::-webkit-scrollbar { display: none; }
    
    @media (max-width: 991px) {
        .category-item { flex: 0 0 30%; }
    }

    @media (max-width: 575px) {
        .category-item { flex: 0 0 45%; }
        .category-wrapper { flex-wrap: wrap; }
    }
</style>