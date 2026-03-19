<section class="py-5">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="display-6 font-serif">{{ $palace->gastronomia_titulo ?? 'A Arte de Servir' }}</h2>
            <div class="bg-gold mx-auto mt-3" style="width: 50px; height: 2px;"></div>
        </div>
        
        <div class="row g-4">
            @php
                // Aqui mapeamos as imagens do seu banco para cada categoria
                $refeicoes = [
                    [
                        'tit' => 'Café da Manhã', 
                        'img' => $palace->bar_imagem_1 ? asset('storage/' . $palace->bar_imagem_1) : 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085', 
                        'desc' => $palace->gastronomia_cafe_desc
                    ],
                    [
                        'tit' => 'Almoço', 
                        'img' => $palace->bar_imagem_2 ? asset('storage/' . $palace->bar_imagem_2) : 'https://images.unsplash.com/photo-1544025162-d76694265947', 
                        'desc' => $palace->gastronomia_almoco_desc
                    ],
                    [
                        'tit' => 'Jantar', 
                        'img' => $palace->bar_imagem_3 ? asset('storage/' . $palace->bar_imagem_3) : 'https://images.unsplash.com/photo-1559339352-11d035aa65de', 
                        'desc' => $palace->gastronomia_jantar_desc
                    ]
                ];
            @endphp
            @foreach($refeicoes as $item)
            <div class="col-md-4" data-aos="fade-up">
                <div class="card border-0 h-100 shadow-sm overflow-hidden group">
                    <div class="ratio ratio-4x3">
                        <img src="{{ $item['img'] }}" class="card-img-top object-fit-cover transition-scale" alt="{{ $item['tit'] }}">
                    </div>
                    <div class="card-body p-4 text-center">
                        <h4 class="font-serif">{{ $item['tit'] }}</h4>
                        <p class="text-secondary small mb-4">{{ $item['desc'] }}</p>
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $palace->contato_whatsapp) }}" class="btn btn-outline-dark rounded-0 px-4">Cardápio</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>