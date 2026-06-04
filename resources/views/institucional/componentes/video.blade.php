<section class="tour-virtual-section" style="padding: 80px 0; background-color: #fcfcfc; border-top: 1px solid #eee;">
    <div class="container text-center">
        <div style="max-width: 1100px; margin: 0 auto;">
            
            <h6 class="sax-subtitle">
                {{ __('messages.video_subtitle') ?? 'Uma Imersão no Luxo' }}
            </h6>
            
            <h2 class="sax-title">
                {{ __('messages.video_title') ?? 'Visite a SAX sem fronteiras' }}
            </h2>
            
            <p class="sax-text">
                {{ __('messages.video_description') ?? 'Explore cada detalhe de nossa loja através de uma experiência 360° imersiva e acompanhe em tempo real o movimento das principais vias de acesso à Ciudad del Este.' }}
            </p>

            <div class="live-videos-grid">
                {{-- Vídeo Ponte da Amizade --}}
                @if(!empty($institucional->iframe_ponte_amizade))
                    <div class="video-card">
                        <h5 class="video-label">
                            <i class="fa fa-video-camera"></i> {{ __('messages.video_label_ponte') ?? 'Ponte da Amizade' }}
                        </h5>
                        <div class="video-responsive-container">
                            {!! $institucional->iframe_ponte_amizade !!}
                        </div>
                    </div>
                @endif

                {{-- Vídeo Centro CDE --}}
                @if(!empty($institucional->iframe_centro_cde))
                    <div class="video-card">
                        <h5 class="video-label">
                            <i class="fa fa-eye"></i> {{ __('messages.video_label_centro') ?? 'Centro de CDE' }}
                        </h5>
                        <div class="video-responsive-container">
                            {!! $institucional->iframe_centro_cde !!}
                        </div>
                    </div>
                @endif
            </div>
            
        </div>
    </div>
</section>