<header class="text-white py-3 background-header">
    <div class="container">
        {{-- Topo: Logo e login/logout --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-3">
            {{-- Logo centralizada --}}
            @if($webpImage)
            <div class="mb-2 mb-md-0 text-center w-100">
                <a href="{{ route('home') }}">
                    <img src="{{ asset('storage/uploads/' . $webpImage) }}" alt="Logo" class="img-fluid" style="max-height:100px;">
                </a>
            </div>
            @endif

            {{-- Login/Logout --}}
            <div class="text-center text-md-end mt-2 mt-md-0">
                @if(Auth::check())
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

        {{-- Menu de navegação Desktop (sem alterações) --}}
        <div class="d-flex flex-wrap justify-content-center gap-2 mb-3 d-none d-md-flex">
            <a href="{{ route('home') }}" class="btn btn-primary"><i class="fa fa-home me-1"></i> Home</a>
            @if(auth()->check())
                @if(auth()->user()->user_type == 1)
                <a href="{{ route('admin.index') }}" class="btn btn-primary"><i class="fa fa-user-shield me-1"></i> Admin</a>
                @else
                <a href="{{ route('user.dashboard') }}" class="btn btn-primary"><i class="fa fa-tachometer-alt me-1"></i> Painel</a>
                @endif
            @endif
            <a href="{{ route('contact.form') }}" class="btn btn-primary"><i class="fa fa-envelope me-1"></i> Contato</a>
            <a href="{{ route('blogs.index') }}" class="btn btn-primary"><i class="fa fa-blog me-1"></i> Blogs</a>
            <a href="{{ route('brands.index') }}" class="btn btn-primary"><i class="fa fa-tag me-1"></i> Marcas</a>
            <a href="{{ route('categories.index') }}" class="btn btn-primary"><i class="fa fa-list me-1"></i> Categorias</a>
            <a href="{{ route('subcategories.index') }}" class="btn btn-primary"><i class="fa fa-layer-group me-1"></i> Subcategorias</a>
            <a href="{{ route('childcategories.index') }}" class="btn btn-primary"><i class="fa fa-sitemap me-1"></i> Categorias Filhas</a>
        </div>

        {{-- Mobile Hamburger --}}
        <div class=" text-center format-header-mobile">
            <button class="d-md-none btn btn-light" id="mobileMenuBtn">
                <i class="fa fa-bars"></i> Menu
            </button>
        

        {{-- Carrinho Desktop (sem alteração) --}}
        @php
        use App\Models\Cart;
        $user = auth()->user();
        $cart = $user ? Cart::with('product')->where('user_id', $user->id)->get() : collect();
        $cartCount = $cart->sum('quantity');
        @endphp

        <div class="d-flex justify-content-center justify-content-md-end position-relative">
            <button id="cart-button" class="btn btn-light">
                <i class="fa fa-shopping-cart me-1"></i> Carrinho ({{ $cartCount }})
            </button>

            @if($cartCount > 0)
            <div id="cart-modal" class="card shadow-lg p-3 position-absolute bg-white text-dark"
                style="top:50px; right:0; display:none; width:100%;width: 22em; z-index:999;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Itens no carrinho</h6>
                    <button id="cart-close" class="btn btn-sm btn-outline-danger">&times;</button>
                </div>
                <ul class="list-group list-group-flush">
                    @foreach($cart as $item)
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>{{ $item->product->title ?? $item->product->slug ?? 'Produto' }}</strong><br>
                                <small>R$ {{ number_format($item->product->price ?? 0, 2, ',', '.') }}</small>
                            </div>
                            <form action="{{ route('cart.remove', $item->product_id) }}" method="POST" onsubmit="return confirm('Remover este item?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa fa-trash"></i></button>
                            </form>
                        </div>
                        <div class="d-flex align-items-center mt-2 gap-2">
                            @if ($item->quantity > 1)
                            <form action="{{ route('cart.update', $item->product_id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="quantity" value="{{ $item->quantity - 1 }}">
                                <button type="submit" class="btn btn-sm btn-outline-secondary">-</button>
                            </form>
                            @endif
                            <span>{{ $item->quantity }}</span>
                            <form action="{{ route('cart.update', $item->product_id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="quantity" value="{{ $item->quantity + 1 }}">
                                <button type="submit" class="btn btn-sm btn-outline-secondary" @if($item->quantity >= ($item->product->stock ?? 1)) disabled @endif>+</button>
                            </form>
                        </div>
                    </li>
                    @endforeach
                </ul>
                <a href="{{ route('cart.view') }}" class="btn btn-primary btn-sm mt-3 w-100"><i class="fa fa-shopping-cart me-1"></i> Ir para o Carrinho</a>
            </div>
            @endif
        </div>
    </div>
{{-- Barra de busca (igual da home) --}}
<form action="{{ route('search') }}" method="GET" class="my-3">
    <div class="input-group">
        <input type="text" 
               name="search" 
               class="form-control" 
               placeholder="Buscar produtos..."
               value="{{ request('search') }}">
        <button class="btn btn-primary" type="submit">
            <i class="fa fa-search"></i>
        </button>
    </div>
</form>

    </div>

    {{-- Drawer Mobile --}}
    <div id="mobileDrawer" class="position-fixed top-0 start-0 vh-100 bg-dark text-white p-3 d-md-none"
        style="width:250px; transform:translateX(-100%); transition:0.3s; z-index:1050;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5>Menu</h5>
            <button class="btn btn-light btn-sm" id="closeDrawer">&times;</button>
        </div>

        {{-- Login / Logout Mobile --}}
        @if(Auth::check())
        <form action="{{ route('logout') }}" method="POST" class="mb-2">
            @csrf
            <button type="submit" class="btn btn-danger w-100 mb-2">
                <i class="fa fa-sign-out-alt me-1"></i> Logout
            </button>
        </form>
        @else
        <button class="btn btn-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#loginModal">
            <i class="fa fa-sign-in-alt me-1"></i> Login
        </button>
        @endif

        {{-- Menu links Mobile --}}
        <div class="d-flex flex-column gap-2">
            <a href="{{ route('home') }}" class="btn btn-primary w-100"><i class="fa fa-home me-1"></i> Home</a>
            @if(auth()->check())
                @if(auth()->user()->user_type == 1)
                <a href="{{ route('admin.index') }}" class="btn btn-primary w-100"><i class="fa fa-user-shield me-1"></i> Admin</a>
                @else
                <a href="{{ route('user.dashboard') }}" class="btn btn-primary w-100"><i class="fa fa-tachometer-alt me-1"></i> Painel</a>
                @endif
            @endif
            <a href="{{ route('contact.form') }}" class="btn btn-primary w-100"><i class="fa fa-envelope me-1"></i> Contato</a>
            <a href="{{ route('blogs.index') }}" class="btn btn-primary w-100"><i class="fa fa-blog me-1"></i> Blogs</a>
            <a href="{{ route('brands.index') }}" class="btn btn-primary w-100"><i class="fa fa-tag me-1"></i> Marcas</a>
            <a href="{{ route('categories.index') }}" class="btn btn-primary w-100"><i class="fa fa-list me-1"></i> Categorias</a>
            <a href="{{ route('subcategories.index') }}" class="btn btn-primary w-100"><i class="fa fa-layer-group me-1"></i> Subcategorias</a>
            <a href="{{ route('childcategories.index') }}" class="btn btn-primary w-100"><i class="fa fa-sitemap me-1"></i> Categorias Filhas</a>
        </div>
    </div>

    {{-- Scripts --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mobile drawer
        const mobileBtn = document.getElementById('mobileMenuBtn');
        const mobileDrawer = document.getElementById('mobileDrawer');
        const closeDrawer = document.getElementById('closeDrawer');

        mobileBtn.addEventListener('click', () => {
            mobileDrawer.style.transform = 'translateX(0)';
        });
        closeDrawer.addEventListener('click', () => {
            mobileDrawer.style.transform = 'translateX(-100%)';
        });
        document.addEventListener('click', e => {
            if(!mobileDrawer.contains(e.target) && e.target !== mobileBtn){
                mobileDrawer.style.transform = 'translateX(-100%)';
            }
        });

        // Carrinho Desktop
        const cartButton = document.getElementById('cart-button');
        const cartModal = document.getElementById('cart-modal');
        const cartClose = document.getElementById('cart-close');

        cartButton?.addEventListener('click', e => {
            e.stopPropagation();
            cartModal.style.display = cartModal.style.display === 'block' ? 'none' : 'block';
        });
        cartClose?.addEventListener('click', () => cartModal.style.display = 'none');
        document.addEventListener('click', event => {
            if(cartModal && !cartModal.contains(event.target) && event.target !== cartButton){
                cartModal.style.display = 'none';
            }
        });
    });
    </script>
</header>

@include('components.modal-login')
