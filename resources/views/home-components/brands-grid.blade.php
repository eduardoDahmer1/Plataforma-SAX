<section class="sax-brands-carousel-section">
    <div class="sax-carousel-container">
        <div class="sax-carousel-3d" id="brandsCarousel">
            {{-- O JS vai inserir as marcas aqui --}}
            <div class="sax-loader">Carregando marcas...</div>
        </div>

        <div class="sax-nav-buttons">
            <button type="button" id="saxPrev">←</button>
            <button type="button" id="saxNext">→</button>
        </div>
    </div>
</section>

<style>
    .sax-brands-carousel-section {
        background-color: #000;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 600px;
        width: 100%;
        overflow: hidden;
        position: relative;
    }

    .sax-carousel-container {
        perspective: 1200px;
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .sax-carousel-3d {
        position: relative;
        width: 300px; 
        height: 420px;
        transform-style: preserve-3d;
    }

    .sax-loader {
        color: white;
        text-align: center;
        width: 100%;
        font-family: sans-serif;
        letter-spacing: 2px;
    }

    .sax-carousel-item {
        position: absolute;
        width: 100%;
        height: 100%;
        transition: all 0.6s cubic-bezier(0.23, 1, 0.32, 1);
        -webkit-box-reflect: below 2px linear-gradient(transparent 70%, rgba(255,255,255,0.1));
        cursor: pointer;
        backface-visibility: hidden;
    }

    .sax-carousel-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border: 1px solid rgba(255,255,255,0.2);
        display: block;
        background-color: #1a1a1a;
    }

    /* Estados 3D */
    .sax-carousel-item.active { transform: translate3d(0, 0, 250px); z-index: 10; opacity: 1; }
    .sax-carousel-item.p1 { transform: translate3d(-105%, 0, 50px) rotateY(45deg); z-index: 5; opacity: 0.7; }
    .sax-carousel-item.n1 { transform: translate3d(105%, 0, 50px) rotateY(-45deg); z-index: 5; opacity: 0.7; }
    .sax-carousel-item.p2 { transform: translate3d(-170%, 0, -150px) rotateY(45deg); z-index: 2; opacity: 0.3; }
    .sax-carousel-item.n2 { transform: translate3d(170%, 0, -150px) rotateY(-45deg); z-index: 2; opacity: 0.3; }
    .sax-carousel-item.hidden { transform: translate3d(0, 0, -500px); opacity: 0; z-index: 0; }

    .sax-nav-buttons {
        margin-top: 40px;
        display: flex;
        gap: 20px;
        z-index: 100;
    }

    .sax-nav-buttons button {
        background: transparent;
        border: 1px solid #ffffff;
        color: white;
        padding: 8px 30px;
        cursor: pointer;
        font-size: 18px;
        transition: 0.3s;
    }

    .sax-nav-buttons button:hover { 
        background: white;
        color: black;
    }
</style>

<script>
(function() {
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('brandsCarousel');
        // Pega os dados do PHP com fallback para array vazio
        const brandsRaw = {!! json_encode($brands) !!} || [];
        const brands = brandsRaw.slice(0, 10);
        
        if (brands.length === 0) {
            container.innerHTML = '<div class="sax-loader">Nenhuma marca disponível.</div>';
            return;
        }

        container.innerHTML = '';

        brands.forEach((brand, i) => {
            const div = document.createElement('div');
            div.className = 'sax-carousel-item hidden';
            
            // CORREÇÃO: Usando a pasta storage/uploads e a propriedade .image
            const imageFile = brand.image ? brand.image : '';
            const photoPath = imageFile.startsWith('http') 
                ? imageFile 
                : `{{ asset('storage/uploads') }}/${imageFile}`;
            
            // Placeholder robusto para evitar 404
            const noImage = "https://placehold.co/400x600/1a1a1a/ffffff?text=SAX";

            div.innerHTML = `
                <a href="{{ url('brand') }}/${brand.slug}">
                    <img src="${photoPath}" 
                         alt="${brand.name}" 
                         onerror="this.src='${noImage}'">
                </a>
            `;
            container.appendChild(div);
        });

        const items = container.querySelectorAll('.sax-carousel-item');
        let currentIndex = 0;

        function update() {
            items.forEach((item, i) => {
                item.className = 'sax-carousel-item hidden';
                let diff = i - currentIndex;
                
                // Lógica de carrossel infinito
                if (diff > items.length / 2) diff -= items.length;
                if (diff < -items.length / 2) diff += items.length;

                if (diff === 0) item.classList.add('active');
                else if (diff === -1) item.classList.add('p1');
                else if (diff === 1) item.classList.add('n1');
                else if (diff === -2) item.classList.add('p2');
                else if (diff === 2) item.classList.add('n2');
            });
        }

        const btnNext = document.getElementById('saxNext');
        const btnPrev = document.getElementById('saxPrev');

        if(btnNext) {
            btnNext.onclick = (e) => {
                e.preventDefault();
                currentIndex = (currentIndex + 1) % items.length;
                update();
            };
        }

        if(btnPrev) {
            btnPrev.onclick = (e) => {
                e.preventDefault();
                currentIndex = (currentIndex - 1 + items.length) % items.length;
                update();
            };
        }

        // Inicializa o estado visual
        update();
    });
})();
</script>