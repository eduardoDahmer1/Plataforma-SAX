{{-- SAX Café & Bistrô — Header / Navegação --}}
<header class="navbar-cafe" id="cafeBistroNav">
    <div class="container">
        <nav class="navbar navbar-expand-lg p-0">

            {{-- Logo --}}
            <a href="{{ route('cafe_bistro.index') }}" class="brand-cafe me-auto">
                SAX Café & Bistrô
            </a>

            {{-- Toggler mobile (padrão Palace) #TODO: mejorar el layout de los elementos y centrar boton reservar --}} 
            <button class="navbar-toggler-cafe d-lg-none me-3" type="button"
                    data-bs-toggle="collapse" data-bs-target="#cafeNav"
                    aria-controls="cafeNav" aria-expanded="false" aria-label="Toggle navigation">
                <i class="bi bi-list text-white fs-4"></i>
            </button>

            {{-- Links (desktop centralizado + colapsable mobile) --}}
            <div class="collapse navbar-collapse" id="cafeNav">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-lg-4">
                    <li class="nav-item">
                        <a href="{{ route('home') }}" class="nav-link-cafe">Loja</a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link-cafe">Institucional</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('blogs.index') }}" class="nav-link-cafe">#SAXNews</a>
                    </li>
                    <li class="nav-item">
                        <a href="#horarios" class="nav-link-cafe">Horários</a>
                    </li>
                </ul>

                <div class="mt-3 mt-lg-0"> <!-- #TODO: ver si puedo colocar una variable en AppService Provider -->
                    <a href="https://wa.me/NUMERO" target="_blank" class="btn-reservar-cafe">
                        Reservar
                    </a>
                </div>
            </div>

        </nav>
    </div>
</header>
