{{-- SAX Bridal — Instagram CTA --}}
<div class="instagram-cta-section" data-reveal="up">
    <p class="instagram-cta-text">¿Quieres ver más novias felices?<br>Descubre historias reales de amor en nuestro Instagram.</p>
    <a href="{{ $instagramUrl }}" target="_blank" rel="noopener noreferrer" class="btn-sax-outline instagram-cta-btn">
        <i class="fab fa-instagram me-2"></i> Visitar Instagram
    </a>
</div>

@push('styles')
<style>
    .instagram-cta-section {
        background: var(--bridal-white);
        text-align: center;
        padding: 0 20px 60px;
    }

    .instagram-cta-section .title-gold {
        opacity: 0.55;
    }

    .instagram-cta-text {
        font-family: var(--font-serif);
        font-size: 0.95rem;
        color: var(--bridal-dark);
        font-style: italic;
        line-height: 1.6;
        margin-bottom: 24px;
        opacity: 0.8;
    }

    .instagram-cta-btn {
        font-size: 0.68rem;
        letter-spacing: 2px;
        padding: 14px 36px;
        background: var(--bridal-gold);
        color: var(--bridal-white);
        border-color: var(--bridal-gold);
    }

    .instagram-cta-btn:hover {
        background: #E1306C;
        border-color: #E1306C;
        color: #fff;
    }
</style>
@endpush
