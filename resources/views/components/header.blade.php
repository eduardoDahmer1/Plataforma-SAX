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


   <div class="container mb-3">
       <a href="{{ route('home') }}" class="btn btn-primary me-2 mb-2">Home</a>
       @if(auth()->check() && auth()->user()->user_type == 1)
           <a href="{{ route('admin.index') }}" class="btn btn-primary me-2 mb-2">Admin</a>
       @endif
       <a href="{{ route('contact.form') }}" class="btn btn-primary me-2 mb-2">Fale Conosco</a>
       <a href="{{ route('blogs.index') }}" class="btn btn-primary me-2 mb-2">Ver Blogs</a>
       <a href="{{ route('brands.index') }}" class="btn btn-primary me-2 mb-2">Marcas</a>
       <a href="{{ route('categories.index') }}" class="btn btn-primary me-2 mb-2">Categorias</a>
       <a href="{{ route('subcategories.index') }}" class="btn btn-primary me-2 mb-2">Subcategorias</a>


       @php
           $cart = session('cart', []);
           $cartCount = count($cart);
       @endphp


       <div class="position-relative d-inline-block ms-3" id="cart-hover-area" style="cursor:pointer;">
       <button id="cart-button" class="btn btn-light">
           🛒 Carrinho ({{ $cartCount }})
       </button>


       @if($cartCount > 0)
       <div id="cart-modal" class="card shadow-lg p-3 position-absolute bg-white text-dark" style="top: 50px; right: 0; display: none; width: 300px; z-index:999;">
           <div class="d-flex justify-content-between align-items-center mb-2">
               <h6 class="mb-0">Itens no carrinho:</h6>
               <button id="cart-close" class="btn btn-sm btn-outline-danger">&times;</button>
           </div>
           <ul class="list-group list-group-flush">
               @foreach($cart as $item)
                   <li class="list-group-item">
                       {{ $item['slug'] ?? 'Produto' }}<br>
                       <small>R$ {{ number_format($item['price'] ?? 0, 2, ',', '.') }}</small><br>
                       Quantidade: {{ $item['quantity'] ?? 1 }}
                   </li>
               @endforeach
           </ul>
           <a href="{{ route('cart.view') }}" class="btn btn-primary btn-sm mt-3 w-100">Ir para o Carrinho</a>
       </div>
       @endif
   </div>
   </div>


   <script>
       document.addEventListener('DOMContentLoaded', function() {
           const cartButton = document.getElementById('cart-button');
           const cartModal = document.getElementById('cart-modal');
           const cartClose = document.getElementById('cart-close');


           function toggleCartModal() {
               if (cartModal && cartModal.style.display === 'block') {
                   cartModal.style.display = 'none';
               } else if (cartModal) {
                   cartModal.style.display = 'block';
               }
           }


           if (cartButton) {
               cartButton.addEventListener('click', function(e) {
                   e.stopPropagation();
                   toggleCartModal();
               });
           }


           if (cartClose) {
               cartClose.addEventListener('click', function() {
                   cartModal.style.display = 'none';
               });
           }


           document.addEventListener('click', function(event) {
               if (cartModal && !cartModal.contains(event.target) && event.target !== cartButton) {
                   cartModal.style.display = 'none';
               }
           });
       });
   </script>
</header>


@include('components.modal-login')