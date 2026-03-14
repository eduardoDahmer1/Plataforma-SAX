<section class="tour-virtual-section" style="padding: 80px 0; background-color: #fcfcfc; border-top: 1px solid #eee;">
    <div class="container text-center">
        <div style="max-width: 1100px; margin: 0 auto;">
            <h6 class="sax-subtitle">Uma Imersão no Luxo</h6>
            <h2 class="sax-title">Visite a SAX sem fronteiras</h2>
            <p class="sax-text">
                Explore cada detalhe de nossa loja através de uma experiência 360° imersiva e acompanhe em tempo real o movimento das principais vias de acesso à Ciudad del Este.
            </p>

            @if($institucional->iframe_tour_360)
            <div class="btn-container-tour" style="margin: 40px 0 60px 0;">
                <button type="button" class="btn-sax-tour main-btn" data-toggle="modal" data-target="#modalCDE">
                    <i class="fa fa-shopping-bag"></i> ABRIR TOUR VIRTUAL 360°
                </button>
            </div>
            @endif

            <div class="live-videos-grid">
                {{-- Vídeo Ponte da Amizade --}}
                @if($institucional->iframe_ponte_amizade)
                <div class="video-card">
                    <h5 class="video-label"><i class="fa fa-video-camera"></i> Ponte da Amizade</h5>
                    <div class="video-responsive-container">
                        {!! $institucional->iframe_ponte_amizade !!}
                    </div>
                </div>
                @endif

                {{-- Vídeo Centro CDE --}}
                @if($institucional->iframe_centro_cde)
                <div class="video-card">
                    <h5 class="video-label"><i class="fa fa-eye"></i> Centro de CDE</h5>
                    <div class="video-responsive-container">
                        {!! $institucional->iframe_centro_cde !!}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- Modal para o Tour 360 --}}
@if($institucional->iframe_tour_360)
<div class="modal fade" id="modalCDE" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content sax-modal-content">
            <div class="modal-header sax-modal-header">
                <h4 class="modal-title">SAX | Ciudad Del Este — Tour Virtual</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body p-0 bg-black">
                <div class="video-responsive-container modal-360">
                    {!! $institucional->iframe_tour_360 !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<style>
@import url('https://fonts.googleapis.com/css2?family=Cormorant:wght@400;600;700&display=swap');

/* Grid de Vídeos */
.live-videos-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-top: 20px;
}

.video-card {
    background: #fff;
    padding: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    border-radius: 4px;
}

.video-label {
    font-family: 'Cormorant', serif;
    font-size: 1.4rem;
    color: #1a1a1a;
    margin-bottom: 15px;
    font-weight: 600;
}

/* Container Responsivo Corrigido */
.video-responsive-container {
    position: relative;
    padding-bottom: 56.25%; /* Proporção 16:9 */
    height: 0;
    overflow: hidden;
    background: #000;
    display: flex;
    justify-content: center;
    align-items: center;
}

/* Reset de qualquer largura fixa que venha do banco de dados */
.video-responsive-container iframe, 
.video-responsive-container object, 
.video-responsive-container embed,
.video-responsive-container div {
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    max-width: 100% !important;
    border: 0;
}

/* Tipografia e Botões */
.sax-subtitle { color: #D7B176; text-transform: uppercase; letter-spacing: 3px; font-weight: 700; margin-bottom: 15px; font-size: 0.9rem; }
.sax-title { font-family: 'Cormorant', serif; font-size: 3.5rem; color: #1a1a1a; line-height: 1.1; margin-bottom: 25px; }
.sax-text { color: #666; font-size: 1.1rem; line-height: 1.7; max-width: 750px; margin: 0 auto; }

.btn-sax-tour.main-btn {
    background-color: #1a1a1a;
    color: #D7B176;
    border: 2px solid #1a1a1a;
    padding: 20px 45px;
    font-weight: 700;
    font-size: 0.9rem;
    letter-spacing: 2px;
    transition: all 0.4s ease;
    cursor: pointer;
    text-transform: uppercase;
}

.btn-sax-tour.main-btn:hover {
    background-color: #D7B176;
    border-color: #D7B176;
    color: #fff;
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.sax-modal-content { border-radius: 0; border: none; }
.sax-modal-header { background: #1a1a1a; color: #D7B176; border: none; padding: 15px 25px; display: flex; align-items: center; justify-content: space-between; }
.sax-modal-header .modal-title { font-family: 'Cormorant', serif; font-size: 1.6rem; margin: 0; }
.sax-modal-header .close { color: #D7B176; opacity: 1; font-size: 2rem; background: transparent; border: none; cursor: pointer; }

@media (max-width: 991px) {
    .live-videos-grid { grid-template-columns: 1fr; }
    .sax-title { font-size: 2.8rem; }
}

@media (max-width: 768px) {
    .sax-title { font-size: 2.2rem; }
    .btn-sax-tour.main-btn { width: 100%; }
}
</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    // Garante que o iframe ocupe todo o espaço mesmo após o carregamento
    $('.video-responsive-container iframe').each(function() {
        $(this).attr('width', '100%').attr('height', '100%');
    });

    $('#modalCDE').on('hidden.bs.modal', function () {
        var $iframe = $(this).find('iframe');
        var src = $iframe.attr('src');
        $iframe.attr('src', '');
        $iframe.attr('src', src);
    });
});
</script>