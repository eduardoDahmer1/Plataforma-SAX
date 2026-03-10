@extends('layout.palace')

@section('content')
<section class="min-vh-100 d-flex align-items-center position-relative overflow-hidden bg-dark">
    <div class="position-absolute top-0 start-0 w-100 h-100 z-0">
        <img src="{{ $palace->hero_imagem ? asset('storage/' . $palace->hero_imagem) : 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b' }}" 
             class="w-100 h-100 object-fit-cover opacity-50" alt="Hero">
    </div>
    
    <div class="container position-relative z-1 text-white py-5">
        <div class="row">
            <div class="col-lg-8 col-xl-7" data-aos="fade-right">
                <span class="text-gold fw-bold text-uppercase tracking-widest mb-3 d-block">Experiência Exclusiva</span>
                <h1 class="display-2 fw-light mb-4">{{ $palace->hero_titulo ?? 'SAX PALACE' }}</h1>
                <p class="lead mb-5 opacity-75 d-none d-md-block">{{ $palace->hero_descricao }}</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $palace->contato_whatsapp) }}" class="btn btn-gold btn-lg px-5 py-3 rounded-0 text-uppercase fw-bold">Reservar</a>
                    <a href="#sobre" class="btn btn-outline-light btn-lg px-5 py-3 rounded-0 text-uppercase fw-bold">Descobrir</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="sobre" class="py-5 py-lg-10">
    <div class="container py-5">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 order-2 order-lg-1" data-aos="fade-up">
                <div class="pe-lg-5">
                    <h2 class="display-5 mb-4 font-serif">{{ $palace->hero_titulo }}</h2>
                    <p class="text-secondary fs-5 mb-4">{{ $palace->hero_descricao }}</p>
                    <div class="row g-4 pt-3">
                        <div class="col-6">
                            <div class="border-start border-gold border-3 ps-3">
                                <h3 class="h2 mb-0">1000+</h3>
                                <p class="small text-uppercase mb-0 text-secondary">Rótulos</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border-start border-gold border-3 ps-3">
                                <h3 class="h2 mb-0">Piso 11</h3>
                                <p class="small text-uppercase mb-0 text-secondary">Vista Prime</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 order-1 order-lg-2" data-aos="zoom-in">
                <div class="position-relative">
                    <img src="{{ $palace->hero_imagem ? asset('storage/' . $palace->hero_imagem) : 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b' }}" 
                         class="img-fluid shadow-lg rounded-3" alt="Palace Interior">
                    <div class="bg-gold position-absolute d-none d-md-block" style="width: 100px; height: 100px; bottom: -20px; right: -20px; z-index: -1;"></div>
                </div>
            </div>
        </div>
    </div>
</section>

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

<section class="py-5">
    <div class="container py-5">
        <div class="row g-3">
            <div class="col-md-6" data-aos="fade-right">
                <div class="h-100 p-5 bg-dark text-white d-flex flex-column justify-content-center">
                    <h2 class="display-5 font-serif mb-4">{{ $palace->eventos_titulo ?? 'Eventos' }}</h2>
                    <p class="lead opacity-75 mb-4">{{ $palace->eventos_descricao }}</p>
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $palace->contato_whatsapp) }}" class="text-gold text-decoration-none fw-bold">SOLICITAR ORÇAMENTO <i class="bi bi-arrow-right ms-2"></i></a>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row g-3">
                    @php 
                        $galeria = is_array($palace->eventos_galeria) ? $palace->eventos_galeria : json_decode($palace->eventos_galeria, true); 
                    @endphp
                    @if(!empty($galeria))
                        @foreach(array_slice($galeria, 0, 4) as $foto)
                        <div class="col-6" data-aos="zoom-in">
                            <img src="{{ asset('storage/' . $foto) }}" class="img-fluid rounded shadow-sm w-100" style="height: 200px; object-fit: cover;">
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection