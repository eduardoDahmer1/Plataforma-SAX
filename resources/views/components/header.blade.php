<header class="bg-dark text-white p-3">
    <div class="container d-flex justify-content-between align-items-center">

        @if ($webpImage)
        <div>
            <a href="{{ route('pages.home') }}">
                <img src="{{ asset('storage/uploads/' . $webpImage) }}" alt="Imagem Header"
                    style="max-height: 100px; display: block; margin-bottom: 10px;">
            </a>
        </div>
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
</header>

@include('components.modal-login')