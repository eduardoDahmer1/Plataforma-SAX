<header class="bg-dark text-white p-3">
    <div class="container d-flex justify-content-between align-items-center">

        {{-- Logo --}}
        @if ($webpImage)
        <div>
            <a href="{{ route('home') }}">
                <img src="{{ asset('storage/uploads/' . $webpImage) }}" alt="Imagem Header"
                    style="max-height: 100px; display: block; margin-bottom: 10px;">
            </a>
        </div>
        @endif

        {{-- Login / Logout --}}
        <div>
            @if (Auth::check())
            <form action="{{ route('logout') }}" method="POST" id="logout-form" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-danger">
                    <i class="fa fa-sign-out-alt"></i> Logout
                </button>
            </form>
            @else
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#loginModal">
                <i class="fa fa-sign-in-alt"></i> Login
            </button>
            @endif
        </div>
    </div>

    {{-- Navegação simplificada --}}
    <div class="container mb-3">
        <a href="{{ route('home') }}" class="btn btn-primary me-2 mb-2">Home</a>

        @if(auth()->check())
            @if(auth()->user()->user_type == 1)
                <a href="{{ route('admin.index') }}" class="btn btn-primary me-2 mb-2">Admin</a>
            @else
                <a href="{{ route('user.dashboard') }}" class="btn btn-primary me-2 mb-2">Painel do Usuário</a>
            @endif
        @endif
    </div>
</header>

@include('components.modal-login')
