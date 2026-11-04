@extends('layout.admin')

@section('title', 'Editor de imagens - Painel Administrativo')

@section('content')
<div class="sax-image-studio" data-image-studio>
    <div class="sax-studio-heading">
        <div>
            <span><i class="fa-solid fa-wand-magic-sparkles"></i> Ferramenta do sistema</span>
            <h1>Editor de imagens</h1>
            <p>Prepare produtos, banners e peças sem sair do painel. O arquivo é processado no navegador e só é baixado quando você finalizar.</p>
        </div>
        <button type="button" class="btn btn-outline-dark" data-studio-reset><i class="fa-solid fa-file-circle-plus me-2"></i>Novo projeto</button>
    </div>

    <div class="row g-4">
        <div class="col-xl-4">
            <div class="sax-premium-card shadow-sm h-100">
                <div class="p-4">
                    <h5 class="fw-bold mb-1">1. Escolha o formato</h5>
                    <p class="text-muted small mb-3">Use um tamanho pronto ou informe as medidas desejadas.</p>

                    <div class="sax-studio-presets">
                        <button type="button" data-studio-preset="1080,1080,contain" class="is-active"><i class="fa-solid fa-box"></i><span><strong>Produto</strong><small>1080 × 1080</small></span></button>
                        <button type="button" data-studio-preset="1920,640,cover"><i class="fa-solid fa-panorama"></i><span><strong>Banner desktop</strong><small>1920 × 640</small></span></button>
                        <button type="button" data-studio-preset="1080,1350,cover"><i class="fa-brands fa-instagram"></i><span><strong>Feed vertical</strong><small>1080 × 1350</small></span></button>
                        <button type="button" data-studio-preset="1080,1920,cover"><i class="fa-solid fa-mobile-screen"></i><span><strong>Story / mobile</strong><small>1080 × 1920</small></span></button>
                    </div>

                    <div class="sax-studio-custom-size">
                        <label><span>Largura</span><input type="number" min="240" max="2000" value="1080" data-studio-width></label>
                        <i class="fa-solid fa-xmark"></i>
                        <label><span>Altura</span><input type="number" min="240" max="2000" value="1080" data-studio-height></label>
                        <label><span>Enquadramento</span><select data-studio-mode><option value="contain">Encaixar sem cortar</option><option value="cover">Preencher e cortar</option></select></label>
                    </div>

                    <h5 class="fw-bold mt-4 mb-1">2. Envie a imagem</h5>
                    <p class="text-muted small mb-3">JPG, PNG ou WebP. Imagens grandes serão ajustadas na exportação.</p>
                    <label class="sax-studio-upload" data-studio-dropzone>
                        <input type="file" accept="image/jpeg,image/png,image/webp" data-studio-input>
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <strong>Arraste ou selecione uma imagem</strong>
                        <small data-studio-file-label>Nenhum arquivo selecionado</small>
                    </label>

                    <div class="sax-studio-actions">
                        <button type="button" class="btn btn-dark" data-studio-edit disabled><i class="fa-solid fa-crop-simple me-2"></i>Abrir editor completo</button>
                        <button type="button" class="btn btn-warning" data-studio-auto disabled><i class="fa-solid fa-wand-magic-sparkles me-2"></i>Ajuste automático</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="sax-premium-card shadow-sm h-100 sax-studio-preview-card">
                <div class="sax-studio-preview-toolbar">
                    <div><strong>Prévia do projeto</strong><span data-studio-output-info>1080 × 1080 px · WebP</span></div>
                    <button type="button" class="btn btn-success" data-studio-download disabled><i class="fa-solid fa-download me-2"></i>Baixar imagem</button>
                </div>
                <div class="sax-studio-preview" data-studio-preview-shell>
                    <div class="sax-studio-empty" data-studio-empty>
                        <i class="fa-regular fa-image"></i>
                        <strong>Sua imagem aparecerá aqui</strong>
                        <span>Depois, use o editor para remover o fundo, recortar, desenhar ou adicionar texto.</span>
                    </div>
                    <img alt="Prévia da imagem editada" data-studio-preview hidden>
                </div>
                <div class="sax-studio-capabilities">
                    <span><i class="fa-solid fa-person-rays"></i> Remover fundo</span>
                    <span><i class="fa-solid fa-draw-polygon"></i> Recorte livre</span>
                    <span><i class="fa-solid fa-eraser"></i> Borracha e restauração</span>
                    <span><i class="fa-solid fa-pencil"></i> Desenho e texto</span>
                    <span><i class="fa-solid fa-gauge-high"></i> Controle de qualidade</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
