@extends('layout.admin')

@section('content')
<div class="admin-banners-wrapper py-4">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Admin</a></li>
                        <li class="breadcrumb-item active">Multimedia</li>
                    </ol>
                </nav>
                <h1 class="h2 fw-bold text-dark mb-0">Gestión de Banners y Logos</h1>
                <p class="text-muted mb-0">Controle a identidade visual e os banners da plataforma em um só lugar.</p>
            </div>
        </div>

        {{-- Alertas --}}
        @if(session('success'))
            <div class="alert alert-modern-success alert-dismissible fade show mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <div class="alert-icon-circle me-3"><i class="fas fa-check"></i></div>
                    <div>{{ session('success') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-modern-danger mb-4" role="alert">
                <div class="fw-bold mb-2"><i class="fas fa-exclamation-circle me-2"></i> Por favor corrija los siguientes errores:</div>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $images = [
                ['field' => 'header_image', 'title' => 'Logo Header', 'category' => 'Identidad', 'file' => $webpImage ?? null, 'routeUpload' => 'admin.header.upload', 'routeDelete' => 'admin.header.delete'],
                ['field' => 'logo_palace', 'title' => 'Logo SAX Palace', 'category' => 'Identidad', 'file' => $logoPalace ?? null, 'routeUpload' => 'admin.logopalace.upload', 'routeDelete' => 'admin.logopalace.delete'],
                ['field' => 'logo_bridal', 'title' => 'Logo SAX Bridal', 'category' => 'Identidad', 'file' => $logoBridal ?? null, 'routeUpload' => 'admin.logobridal.upload', 'routeDelete' => 'admin.logobridal.delete'],
                ['field' => 'banner_horizontal', 'title' => 'Banner Horizontal', 'category' => 'Identidad', 'file' => $bannerHorizontal ?? null, 'routeUpload' => 'admin.bannerhorizontal.upload', 'routeDelete' => 'admin.bannerhorizontal.delete'],

                ['field' => 'icon_info', 'title' => 'Ícone Info/Relógio', 'category' => 'Sistema', 'file' => $attribute->icon_info ?? null, 'routeUpload' => 'admin.icon_info.upload', 'routeDelete' => 'admin.icon_info.delete'],
                ['field' => 'icon_cabide', 'title' => 'Ícone Cabide', 'category' => 'Sistema', 'file' => $attribute->icon_cabide ?? null, 'routeUpload' => 'admin.icon_cabide.upload', 'routeDelete' => 'admin.icon_cabide.delete'],
                ['field' => 'icon_help', 'title' => 'Ícone Ajuda', 'category' => 'Sistema', 'file' => $attribute->icon_help ?? null, 'routeUpload' => 'admin.icon_help.upload', 'routeDelete' => 'admin.icon_help.delete'],
                ['field' => 'noimage', 'title' => 'Noimage Default', 'category' => 'Sistema', 'file' => $noimage ?? null, 'routeUpload' => 'admin.noimage.upload', 'routeDelete' => 'admin.noimage.delete'],

                ['field' => 'banner1', 'title' => 'Slider Home 01', 'category' => 'Home', 'file' => $banners['banner1'] ?? null, 'routeUpload' => 'admin.banner1.upload', 'routeDelete' => 'admin.banner1.delete'],
                ['field' => 'banner2', 'title' => 'Slider Home 02', 'category' => 'Home', 'file' => $banners['banner2'] ?? null, 'routeUpload' => 'admin.banner2.upload', 'routeDelete' => 'admin.banner2.delete'],
                ['field' => 'banner3', 'title' => 'Slider Home 03', 'category' => 'Home', 'file' => $banners['banner3'] ?? null, 'routeUpload' => 'admin.banner3.upload', 'routeDelete' => 'admin.banner3.delete'],
                ['field' => 'banner4', 'title' => 'Slider Home 04', 'category' => 'Home', 'file' => $banners['banner4'] ?? null, 'routeUpload' => 'admin.banner4.upload', 'routeDelete' => 'admin.banner4.delete'],
                ['field' => 'banner5', 'title' => 'Slider Home 05', 'category' => 'Home', 'file' => $banners['banner5'] ?? null, 'routeUpload' => 'admin.banner5.upload', 'routeDelete' => 'admin.banner5.delete'],
                ['field' => 'banner6', 'title' => 'Banner Principal 06', 'category' => 'Home', 'file' => $banners['banner6'] ?? null, 'routeUpload' => 'admin.banner6.upload', 'routeDelete' => 'admin.banner6.delete'],
                ['field' => 'banner7', 'title' => 'Banner Principal 07', 'category' => 'Home', 'file' => $banners['banner7'] ?? null, 'routeUpload' => 'admin.banner7.upload', 'routeDelete' => 'admin.banner7.delete'],
                ['field' => 'banner8', 'title' => 'Banner Principal 08', 'category' => 'Home', 'file' => $banners['banner8'] ?? null, 'routeUpload' => 'admin.banner8.upload', 'routeDelete' => 'admin.banner8.delete'],
                ['field' => 'banner9', 'title' => 'Banner Principal 09', 'category' => 'Home', 'file' => $banners['banner9'] ?? null, 'routeUpload' => 'admin.banner9.upload', 'routeDelete' => 'admin.banner9.delete'],
                ['field' => 'banner10', 'title' => 'Banners Internas', 'category' => 'Home', 'file' => $banners['banner10'] ?? null, 'routeUpload' => 'admin.banner10.upload', 'routeDelete' => 'admin.banner10.delete'],
            ];
            
            $categories = ['Identidad', 'Home', 'Sistema'];
        @endphp

        <ul class="nav nav-pills-custom mb-4" id="bannerTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#all">Todos</button>
            </li>
            @foreach($categories as $cat)
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#cat-{{ Str::slug($cat) }}">{{ $cat }}</button>
                </li>
            @endforeach
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="all">
                <div class="row g-4">
                    @foreach($images as $img)
                        @include('admin.partials.banner_card', ['img' => $img])
                    @endforeach
                </div>
            </div>

            @foreach($categories as $cat)
                <div class="tab-pane fade" id="cat-{{ Str::slug($cat) }}">
                    <div class="row g-4">
                        @foreach($images as $img)
                            @if($img['category'] == $cat)
                                @include('admin.partials.banner_card', ['img' => $img])
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Script para trocar o texto do arquivo selecionado --}}
<script>
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('custom-file-input')) {
            let fileName = e.target.files[0].name;
            let label = e.target.closest('.upload-wrapper').querySelector('.btn-upload-label');
            if(label) label.innerHTML = `<i class="fas fa-file-image me-1"></i> ${fileName}`;
        }
    });
</script>
@endsection

<style>
/* Layout Base */
.admin-banners-wrapper {
    background-color: #f4f7fa;
    min-height: 100vh;
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
}

/* Tabs Estilizadas */
.nav-pills-custom .nav-link {
    color: #64748b;
    font-weight: 600;
    padding: 0.6rem 1.2rem;
    border-radius: 8px;
    margin-right: 8px;
    transition: all 0.3s ease;
}
.nav-pills-custom .nav-link.active {
    background-color: #000;
    color: #fff;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

/* Card Moderno */
.banner-admin-card {
    background: #fff;
    border: none;
    border-radius: 16px;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    border: 1px solid rgba(0,0,0,0.05);
}

.banner-admin-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.08) !important;
}

/* Badge de Categoria */
.category-tag {
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    color: #3b82f6;
    background: #eff6ff;
    padding: 2px 8px;
    border-radius: 4px;
}

/* Área de Preview */
.preview-container {
    position: relative;
    height: 180px;
    background: #f8fafc;
    background-image: radial-gradient(#e2e8f0 1px, transparent 1px);
    background-size: 16px 16px; /* Grid sutil para logos */
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.banner-preview-img {
    max-width: 90%;
    max-height: 85%;
    object-fit: contain;
    filter: drop-shadow(0 5px 10px rgba(0,0,0,0.05));
    transition: transform 0.3s ease;
}

.banner-admin-card:hover .banner-preview-img {
    transform: scale(1.05);
}

/* Overlay de Deleção */
.preview-overlay {
    position: absolute;
    top: 10px;
    right: 10px;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.banner-admin-card:hover .preview-overlay {
    opacity: 1;
}

/* Form de Upload Minimalista */
.upload-wrapper {
    position: relative;
}

.custom-file-input {
    position: absolute;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
    z-index: 2;
}

.btn-upload-label {
    display: block;
    background: #f8fafc;
    border: 1.5px dashed #cbd5e1;
    color: #64748b;
    padding: 10px;
    border-radius: 10px;
    text-align: center;
    font-size: 0.8rem;
    font-weight: 500;
    transition: all 0.2s;
}

.upload-wrapper:hover .btn-upload-label {
    border-color: #000;
    color: #000;
    background: #f1f5f9;
}

/* Alertas Modernos */
.alert-modern-success {
    background-color: #ecfdf5;
    border: none;
    border-left: 4px solid #10b981;
    color: #065f46;
    border-radius: 12px;
}

.alert-icon-circle {
    width: 32px;
    height: 32px;
    background: #10b981;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-submit-action {
    border-radius: 10px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    font-size: 0.75rem;
}
</style>