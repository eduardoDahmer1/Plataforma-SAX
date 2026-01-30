@if(isset($brands) && $brands->count() > 0)
<section class="sax-brands-section">
    <h2 class="sax-main-title">TUS MARCAS RECOMENDADAS</h2>

    <div class="sax-carousel-master">
        <div class="sax-carousel-3d" id="brandsCarousel">
            {{-- Injetado via JS --}}
        </div>

        {{-- Overlay de Nome e Navegação --}}
        <div class="sax-carousel-footer">
            <div id="saxBrandName" class="sax-brand-label"></div>
            
            <div class="sax-controls">
                <button type="button" id="saxPrev" class="sax-nav-btn">←</button>
                <div class="sax-indicators" id="saxDots"></div>
                <button type="button" id="saxNext" class="sax-nav-btn">→</button>
            </div>
        </div>
    </div>
</section>

<style>
    /* Reset local e Container Principal */
    .sax-brands-section {
        background-color: #000;
        width: 100%;
        min-height: 850px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        padding: 80px 0;
        font-family: 'Inter', sans-serif;
    }

    .sax-main-title {
    color: #fff;
    font-weight: 300;
    letter-spacing: 6px;
    font-size: 2em;
    margin-bottom: 4em;
    text-transform: uppercase;
    }

    .sax-carousel-master {
        perspective: 1500px; /* Profundidade acentuada para efeito 3D */
        width: 100%;
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .sax-carousel-3d {
        position: relative;
        width: 320px; /* Largura base idêntica ao seu modelo */
        height: 480px;
        transform-style: preserve-3d;
        margin-bottom: 20px;
    }

    .sax-item {
        position: absolute;
        width: 100%;
        height: 100%;
        transition: all 0.8s cubic-bezier(0.25, 1, 0.5, 1);
        cursor: pointer;
        backface-visibility: hidden;
        background-color: white;
        /* Reflexo elegante abaixo da imagem */
        -webkit-box-reflect: below 4px linear-gradient(transparent 70%, rgba(255,255,255,0.15));
    }

    .sax-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border: 1px solid rgba(255,255,255,0.1);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.8);
        background-color: white;
    }

    /* POSICIONAMENTO DINÂMICO (3D ESTRITO) */
    
    /* Centro: Grande e Próximo */
    .sax-item.active {
        transform: translate3d(0, 0, 250px);
        z-index: 10;
        opacity: 1;
    }

    /* Laterais Imediatas: Curvatura de 45 graus */
    .sax-item.p1 {
        transform: translate3d(-105%, 0, 0);
        z-index: 5;
        opacity: 0.6;
    }
    .sax-item.n1 {
        transform: translate3d(105%, 0, 0);
        z-index: 5;
        opacity: 0.6;
    }

    /* Longe: Quase sumindo no fundo */
    .sax-item.p2 {
        transform: translate3d(-180%, 0, -250px);
        z-index: 2;
        opacity: 0.2;
    }
    .sax-item.n2 {
        transform: translate3d(180%, 0, -250px) rotateY(-45deg);
        z-index: 2;
        opacity: 0.2;
    }

    .sax-item.hidden {
        transform: translate3d(0, 0, -600px);
        opacity: 0;
        z-index: 0;
        pointer-events: none;
    }

    /* Rodapé: Nome da Marca e Botões */
    .sax-carousel-footer {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-top: 60px;
    }

    .sax-brand-label {
        color: #fff;
        font-size: 22px;
        letter-spacing: 4px;
        text-transform: uppercase;
        margin-bottom: 30px;
        font-weight: 400;
        height: 30px;
    }

    .sax-controls {
        display: flex;
        align-items: center;
        gap: 40px;
    }

    .sax-nav-btn {
        background: none;
        border: 1px solid rgba(255,255,255,0.3);
        color: #fff;
        padding: 8px 30px;
        font-size: 20px;
        cursor: pointer;
        transition: 0.3s ease;
    }

    .sax-nav-btn:hover {
        border-color: #fff;
        background: rgba(255,255,255,0.1);
    }

    /* Indicadores (Dots) */
    .sax-indicators {
        display: flex;
        gap: 10px;
    }

    .sax-dot {
        width: 6px;
        height: 6px;
        background: rgba(255,255,255,0.3);
        border-radius: 50%;
        transition: 0.3s;
    }

    .sax-dot.active {
        background: #fff;
        transform: scale(1.5);
    }
</style>

<script>
(function() {
    window.addEventListener('load', function() {
        const container = document.getElementById('brandsCarousel');
        const nameDisplay = document.getElementById('saxBrandName');
        const dotsContainer = document.getElementById('saxDots');
        
        // Dados reais do Laravel
        const brands = {!! $brands->toJson() !!};
        if (!brands || brands.length === 0) return;

        let currentIndex = 0;

        // 1. Criar Itens e Dots
        brands.forEach((brand, i) => {
            // Criar Slide
            const div = document.createElement('div');
            div.className = 'sax-item hidden';
            div.setAttribute('data-name', brand.name);
            
            // Lógica de URL da imagem baseada nos seus prints
            const imgFile = brand.image || brand.banner || '';
            const imgPath = imgFile.startsWith('http') ? imgFile : `{{ asset('storage') }}/${imgFile.replace(/^\/+/, '')}`;

            div.innerHTML = `
                <a href="{{ url('brand') }}/${brand.slug || brand.id}">
                    <img src="${imgPath}" alt="${brand.name}" onerror="this.src='https://placehold.co/400x600/111/fff?text=${brand.name}'">
                </a>
            `;
            container.appendChild(div);

            // Criar Dot
            const dot = document.createElement('div');
            dot.className = 'sax-dot';
            dotsContainer.appendChild(dot);
        });

        const items = container.querySelectorAll('.sax-item');
        const dots = dotsContainer.querySelectorAll('.sax-dot');

        function updateCarousel() {
            items.forEach((item, i) => {
                item.className = 'sax-item hidden';
                dots[i].classList.remove('active');

                let diff = i - currentIndex;
                
                // Lógica circular infinita
                if (diff > brands.length / 2) diff -= brands.length;
                if (diff < -brands.length / 2) diff += brands.length;

                if (diff === 0) {
                    item.className = 'sax-item active';
                    nameDisplay.innerText = item.getAttribute('data-name');
                    dots[i].classList.add('active');
                }
                else if (diff === -1) item.className = 'sax-item p1';
                else if (diff === 1) item.className = 'sax-item n1';
                else if (diff === -2) item.className = 'sax-item p2';
                else if (diff === 2) item.className = 'sax-item n2';
            });
        }

        // Eventos
        document.getElementById('saxNext').addEventListener('click', () => {
            currentIndex = (currentIndex + 1) % brands.length;
            updateCarousel();
        });

        document.getElementById('saxPrev').addEventListener('click', () => {
            currentIndex = (currentIndex - 1 + brands.length) % brands.length;
            updateCarousel();
        });

        // Click na imagem para focar
        items.forEach((item, index) => {
            item.addEventListener('click', (e) => {
                if (!item.classList.contains('active')) {
                    e.preventDefault();
                    currentIndex = index;
                    updateCarousel();
                }
            });
        });

        // Auto-play opcional (Luxury feel)
        setInterval(() => {
            currentIndex = (currentIndex + 1) % brands.length;
            updateCarousel();
        }, 5000);

        updateCarousel();
    });
})();
</script>
@endif