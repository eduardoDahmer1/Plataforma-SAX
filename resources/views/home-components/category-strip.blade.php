<section class="sax-category-strip py-5">
    <div class="container-fluid px-lg-5">
        <div class="category-wrapper">
            @foreach($categories as $cat)
                <a href="{{ url('category/' . $cat->slug) }}" class="category-item">
                    <div class="category-img-box">
                        @php
                            // Lógica Robusta: Verifica se o arquivo existe na pasta storage
                            $internalPath = 'storage/uploads/' . $cat->image;
                            if ($cat->image && file_exists(public_path($internalPath))) {
                                $imagePath = asset($internalPath);
                            } else {
                                // Se não existir, usa um placeholder externo (evita erro 404 no console)
                                $imagePath = "https://placehold.co/400x400/f2f2f2/a3a3a3?text=SAX";
                            }
                        @endphp
                        
                        <img src="{{ $imagePath }}" 
                             alt="{{ $cat->name }}"
                             loading="lazy"
                             onerror="this.src='https://placehold.co/400x400/f2f2f2/a3a3a3?text={{ urlencode($cat->name) }}'">
                    </div>
                    <span class="category-name">{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </div>
</section>

<style>
    .sax-category-strip { background-color: #fff; padding-bottom: 2rem !important; }

    .category-wrapper {
        display: flex;
        justify-content: center;
        gap: 20px;
        flex-wrap: nowrap; /* Mantém em linha no desktop como na imagem */
        overflow-x: auto; /* Permite scroll no mobile se necessário */
    }

    .category-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-decoration: none !important;
        flex: 0 0 18%; /* 5 itens por linha */
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
        font-weight: 700; /* Bold como na imagem */
        color: #000;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        text-align: center;
        margin-top: 5px;
    }

    /* Scrollbar invisível para o wrapper no mobile */
    .category-wrapper::-webkit-scrollbar { display: none; }
    
    @media (max-width: 991px) {
        .category-item { flex: 0 0 30%; }
    }

    @media (max-width: 575px) {
        .category-item { flex: 0 0 45%; }
        .category-wrapper { flex-wrap: wrap; }
    }
</style>