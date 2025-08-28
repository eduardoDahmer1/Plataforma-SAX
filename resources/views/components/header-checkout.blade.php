<header class="text-white py-3 background-header">
    <x-currency-selector />
    <div class="container">

        {{-- Topo: Logo e login/logout --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-3">

            {{-- Logo centralizada --}}
            @if ($webpImage)
            <div class="mb-2 mb-md-0 text-center w-100">
                <a href="{{ route('home') }}">
                    <img src="{{ asset('storage/uploads/' . $webpImage) }}" alt="Imagem Header" 
                         class="img-fluid" style="max-height:100px;">
                </a>
            </div>
            @endif

            {{-- Login/Logout --}}
            <div class="text-center text-md-end mt-2 mt-md-0">
                @if (Auth::check())
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        <i class="fa fa-sign-out-alt me-1"></i> Logout
                    </button>
                </form>
                @else
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#loginModal">
                    <i class="fa fa-sign-in-alt me-1"></i> Login
                </button>
                @endif
            </div>

        </div>

        {{-- Menu de navegação --}}
        <div class="d-flex flex-wrap justify-content-center gap-2 mb-3">
            <a href="{{ route('home') }}" class="btn btn-primary"><i class="fa fa-home me-1"></i> Home</a>

            @if(auth()->check())
                @if(auth()->user()->user_type == 1)
                <a href="{{ route('admin.index') }}" class="btn btn-primary"><i class="fa fa-user-shield me-1"></i> Admin</a>
                @else
                <a href="{{ route('user.dashboard') }}" class="btn btn-primary"><i class="fa fa-tachometer-alt me-1"></i> Painel</a>
                @endif
            @endif
        </div>
        
    </div>
</header>

@include('components.modal-login')
