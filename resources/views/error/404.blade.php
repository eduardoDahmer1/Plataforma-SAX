@extends('layout.layout')

@section('content')
<style>
    .sax-luxury-page {
        font-family: 'Montserrat', 'Helvetica Neue', Arial, sans-serif;
        background-color: #ffffff;
        color: #111111;
        min-height: 70vh;
        display: flex;
        align-items: center;
        padding: 80px 0;
    }
    .sax-lx-brand {
        font-size: 3.5rem;
        font-weight: 300;
        text-transform: uppercase;
        letter-spacing: 0.25em;
        color: #1a1a1a;
        margin-bottom: 2rem;
        line-height: 1.2;
    }
    .sax-lx-divider {
        width: 60px;
        height: 1px;
        background-color: #c5a059;
        margin: 0 auto 2.5rem auto;
    }
    .sax-lx-text {
        font-size: 1.15rem;
        font-weight: 400;
        line-height: 1.8;
        color: #4a4a4a;
        max-width: 750px;
        margin: 0 auto 1.5rem auto;
        letter-spacing: 0.02em;
    }
    .sax-lx-highlight {
        color: #c5a059;
        font-weight: 500;
    }
    .sax-lx-nav-box {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 30px;
        margin-top: 3.5rem;
    }
    .sax-lx-link {
        font-size: 0.95rem;
        text-transform: uppercase;
        letter-spacing: 0.15em;
        color: #222222;
        text-decoration: none;
        position: relative;
        padding-bottom: 8px;
        transition: color 0.3s ease;
        font-weight: 400;
    }
    .sax-lx-link::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 0;
        height: 1px;
        background-color: #c5a059;
        transition: width 0.4s cubic-bezier(0.25, 1, 0.5, 1);
    }
    .sax-lx-link:hover {
        color: #c5a059;
    }
    .sax-lx-link:hover::after {
        width: 100%;
    }
    .sax-lx-icon-group {
        display: flex;
        justify-content: center;
        gap: 40px;
        margin-top: 4rem;
        color: #333333;
    }
    .sax-lx-icon-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #666666;
        transition: color 0.3s;
    }
    .sax-lx-icon-item:hover {
        color: #c5a059;
    }
    .sax-lx-icon-svg {
        width: 28px;
        height: 28px;
        stroke-width: 1.2px;
        fill: none;
        stroke: currentColor;
    }
</style>

<div class="container-fluid sax-luxury-page">
    <div class="row w-100 justify-content-center m-0">
        <div class="col-12 col-xl-9 text-center px-4">
            
            <h1 class="sax-lx-brand animate__animated animate__fadeInUp">Sax Department Store</h1>
            
            <div class="sax-lx-divider animate__animated animate__fadeInUp animate__delay-1s"></div>
            
            <div class="animate__animated animate__fadeInUp animate__delay-2s">
                <p class="sax-lx-text">
                    Seja bem-vindo ao novo e exclusivo portal da <span class="sax-lx-highlight">Sax Department Store</span>. Estamos redefinindo os padrões do luxo digital para proporcionar a você uma experiência de navegação e compras verdadeiramente inesquecível.
                </p>
                <p class="sax-lx-text">
                    Nossa plataforma encontra-se em fase de aprimoramento contínuo. Nossos especialistas estão trabalhando diligentemente nos bastidores para estruturar coleções, catálogos e novidades alinhadas à sofisticação que você merece.
                </p>
                <p class="sax-lx-text">
                    Convidamos você a redescobrir nossos departamentos e a explorar o requinte do nosso universo através das seções dedicadas abaixo.
                </p>
            </div>

            <nav class="sax-lx-nav-box animate__animated animate__fadeInUp animate__delay-3s">
                <a href="{{ route('home') }}" class="sax-lx-link">Início</a>
                <a href="{{ route('categories.index') }}" class="sax-lx-link">Categorias</a>
                <a href="{{ route('brands.index') }}" class="sax-lx-link">Marcas</a>
                <a href="{{ route('palace.index') }}" class="sax-lx-link">Sax Palace</a>
                <a href="{{ route('bridal.index') }}" class="sax-lx-link">Bridal</a>
                <a href="{{ route('cafe_bistro.index') }}" class="sax-lx-link">Café & Bistrô</a>
                <a href="{{ route('contact.form') }}" class="sax-lx-link">Contato</a>
            </nav>

            <div class="sax-lx-icon-group animate__animated animate__fadeInUp animate__delay-4s">
                <div class="sax-lx-icon-item" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#loginModal">
                    <svg class="sax-lx-icon-svg" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    <span>Conta</span>
                </div>
                @if (Auth::check())
                <a href="{{ route('user.preferences') }}" class="sax-lx-icon-item text-decoration-none">
                    <svg class="sax-lx-icon-svg" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                    <span>Favoritos</span>
                </a>
                @endif
                <a href="{{ route('cart.view') }}" class="sax-lx-icon-item text-decoration-none">
                    <svg class="sax-lx-icon-svg" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                    <span>Sacola</span>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        console.log('Luxury 404 Page Rendered Successfully');
    });
</script>
@endsection