<!-- ============================================
    Scripts Master — SAX Plataforma
    Punto único de carga de scripts para todos los layouts.
    Mismo patrón que head-master.blade.php
    ============================================ -->

<!-- 1. jQuery (solo donde se necesita) -->
@if(Route::is('admin.*') || Route::is('manutencao') || Request::is('*palace*'))
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
@endif

<!-- 2. Bootstrap JS (universal) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- 3. Swiper JS (store + temáticas, no admin/checkout/dashboard/manutencao) -->
@unless(Route::is('admin.*') || Route::is('checkout.*') || Route::is('user.*') || Route::is('dashboard') || Route::is('manutencao'))
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
@endunless

<!-- 4. AOS — Animate On Scroll (bridal, palace, institucional) -->
@if(Request::is('*bridal*') || Request::is('*palace*') || Request::is('*institucional*'))
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
@endif

<!-- 5. Fancybox (institucional) -->
@if(Request::is('*institucional*'))
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
@endif

<!-- 6. Trumbowyg + Plugins (admin — también carga Swiper pues app-custom.js lo necesita) -->
@if(Route::is('admin.*') || Route::is('manutencao'))
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/trumbowyg.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/plugins/upload/trumbowyg.upload.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/plugins/resizimg/trumbowyg.resizimg.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/plugins/autogrow/trumbowyg.autogrow.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/plugins/allowtagsfrompaste/trumbowyg.allowtagsfrompaste.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/plugins/fullscreen/trumbowyg.fullscreen.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/plugins/fontsize/trumbowyg.fontsize.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/plugins/fontfamily/trumbowyg.fontfamily.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/plugins/color/trumbowyg.color.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/plugins/table/trumbowyg.table.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/plugins/emoji/trumbowyg.emoji.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/plugins/history/trumbowyg.history.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/plugins/preformatted/trumbowyg.preformatted.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/plugins/template/trumbowyg.template.min.js"></script>
    <script src="{{ asset('js/app-custom.js') }}"></script>
@endif

<!-- 7. Scripts por sección -->

<!-- Home y detalle de producto (únicos que usan .blogSwiper, .productSwiper, .accordion-trigger) -->
@if(Route::is('home') || Route::is('produto.show'))
    <script src="{{ asset('js/home.js') }}"></script>
@endif

<!-- Temáticas -->
@if(Request::is('*institucional*'))
    <script src="{{ asset('js/institucional.js') }}?v={{ file_exists(public_path('js/institucional.js')) ? filemtime(public_path('js/institucional.js')) : time() }}"></script>
@endif

@if(Request::is('*palace*'))
    <script src="{{ asset('js/palace.js') }}"></script>
@endif

@if(Request::is('*bridal*'))
    <script src="{{ asset('js/bridal.js') }}"></script>
@endif

@if(Request::is('*cafe*') || Request::is('*bistro*'))
    <script src="{{ asset('js/bistro.js') }}"></script>
@endif

<!-- Checkout -->
@if(Route::is('checkout.*'))
    <script src="{{ asset('js/app-custom-checkout.js') }}"></script>
@endif

<!-- 8. Stack para scripts inyectados desde vistas child (@push('scripts')) -->
@stack('scripts')
