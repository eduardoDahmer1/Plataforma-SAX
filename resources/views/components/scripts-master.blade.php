<!-- ============================================
    Scripts Master — SAX Plataforma
    Punto único de carga de scripts para todos los layouts.
    Mismo patrón que head-master.blade.php
    ============================================ -->

<!-- 1. jQuery (solo donde se necesita) -->
@if(Route::is('admin.*') || Route::is('manutencao') || (Request::is('*palace*') && !Route::is('admin.*')))
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
@endif

<!-- 2. Bootstrap JS (universal) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- 3. Swiper JS (público + admin, no checkout/dashboard/manutencao) -->
@if(!Route::is('checkout.*') && !Route::is('user.*') && !Route::is('dashboard') && !Route::is('manutencao'))
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
@endif

<!-- 4. AOS — Animate On Scroll (bridal, palace, institucional — solo público) -->
@if(!Route::is('admin.*') && (Request::is('*bridal*') || Request::is('*palace*') || Request::is('*institucional*')))
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
@endif

<!-- 5. Fancybox (institucional — solo público) -->
@if(!Route::is('admin.*') && Request::is('*institucional*'))
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
@endif

<!-- 6. app-custom.js — Scripts globales del frontend (todas las rutas excepto checkout) -->
@if(!Route::is('checkout.*'))
    <script src="{{ asset('js/app-custom.js') }}"></script>
@endif

<!-- 7. TinyMCE + admin.js (solo admin) -->
@if(Route::is('admin.*') || Route::is('manutencao'))
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.10.7/tinymce.min.js"></script>
    <script src="{{ asset('js/admin.js') }}?v={{ filemtime(public_path('js/admin.js')) }}"></script>
@endif

<!-- 8. Scripts por sección -->

<!-- Home y detalle de producto -->
@if(Route::is('home') || Route::is('produto.show'))
    <script src="{{ asset('js/home.js') }}"></script>
@endif

<!-- Temáticas (solo público — admin usa admin.js) -->
@if(!Route::is('admin.*') && Request::is('*institucional*'))
    <script src="{{ asset('js/institucional.js') }}?v={{ file_exists(public_path('js/institucional.js')) ? filemtime(public_path('js/institucional.js')) : time() }}"></script>
@endif

@if(!Route::is('admin.*') && Request::is('*palace*'))
    <script src="{{ asset('js/palace.js') }}"></script>
@endif

@if(!Route::is('admin.*') && Request::is('*bridal*'))
    <script src="{{ asset('js/bridal.js') }}"></script>
@endif

@if(!Route::is('admin.*') && (Request::is('*cafe*') || Request::is('*bistro*')))
    <script src="{{ asset('js/bistro.js') }}"></script>
@endif

<!-- Checkout -->
@if(Route::is('checkout.*'))
    <script src="{{ asset('js/app-custom-checkout.js') }}"></script>
@endif

<!-- 9. Stack para scripts inyectados desde vistas child (@push('scripts')) -->
@stack('scripts')
