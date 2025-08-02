<header class="bg-dark text-white p-3">
    <div class="container d-flex justify-content-between align-items-center">

        @if ($webpImage)
        <div>
            <a href="{{ route('home') }}">
                <img src="{{ asset('storage/uploads/' . $webpImage) }}" alt="Imagem Header"
                style="max-height: 100px; display: block; margin-bottom: 10px;">
            </a>
        </div>
        @endif

        @if (Auth::check())
        <!-- Botão de Logout (aparece se estiver logado) -->
        <form action="{{ route('logout') }}" method="POST" id="logout-form" style="display: inline;">
            @csrf
            <button type="submit" class="btn btn-danger">
                <i class="fa fa-sign-out-alt"></i> Logout
            </button>
        </form>
        @else
        <!-- Botão de Login que abre o Modal -->
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#loginModal">
            <i class="fa fa-sign-in-alt"></i> Login
        </button>
        @endif
    </div>
    <div class="container">
    <a href="{{ route('home') }}" class="btn btn-primary mb-3">Home</a>
    {{-- Verifica se o usuário é admin master --}}
    @if(auth()->check() && auth()->user()->user_type == 1)
    <!-- Exibir botão apenas para admin master -->
    <a href="{{ route('admin.index') }}" class="btn btn-primary mb-3">Admin</a>
    @endif
    <a href="{{ route('contact.form') }}" class="btn btn-primary mb-3">Fale Conosco</a>
    <a href="{{ route('blogs.index') }}" class="btn btn-primary mb-3">Ver Blogs</a>
    <a href="{{ route('brands.index') }}" class="btn btn-primary mb-3">Marcas</a>
    <a href="{{ route('categories.index') }}" class="btn btn-primary mb-3">Categorias</a>
    </div>
</header>

@include('components.modal-login')