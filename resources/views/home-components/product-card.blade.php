{{-- Carrosséis usam exatamente o mesmo card da busca; muda somente o contêiner externo exigido pelo Swiper. --}}
<x-product-card
    :item="$item"
    :cartItems="$cartItems ?? []"
    gridClass="swiper-slide h-auto"
/>
