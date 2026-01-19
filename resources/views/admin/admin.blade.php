@extends('layout.admin')

@section('content')
<div class="admin-banners-wrapper py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Gestión de Banners y Logos</h1>
            <p class="text-muted small">Administre las imágenes principales de la plataforma</p>
        </div>
        <span class="badge bg-soft-dark text-dark px-3 py-2 border">Admin Panel</span>
    </div>

    {{-- Alertas Modernas --}}
    @if(session('success'))
        <div class="alert alert-sax-success d-flex align-items-center mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-sax-danger mb-4" role="alert">
            <div class="fw-bold mb-1"><i class="fas fa-exclamation-triangle me-2"></i> Errores encontrados:</div>
            <ul class="mb-0 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        @php
            $images = [
                ['field' => 'header_image', 'title' => 'Logo Header', 'category' => 'Identidad', 'file' => $webpImage, 'routeUpload' => 'admin.header.upload', 'routeDelete' => 'admin.header.delete'],
                ['field' => 'logo_palace', 'title' => 'Logo SAX Palace', 'category' => 'Identidad', 'file' => $logoPalace, 'routeUpload' => 'admin.logopalace.upload', 'routeDelete' => 'admin.logopalace.delete'],
                ['field' => 'noimage', 'title' => 'Noimage Default', 'category' => 'Sistema', 'file' => $noimage, 'routeUpload' => 'admin.noimage.upload', 'routeDelete' => 'admin.noimage.delete'],
                ['field' => 'banner1', 'title' => 'Banner Principal 01', 'category' => 'Home', 'file' => $banners['banner1'] ?? null, 'routeUpload' => 'admin.banner1.upload', 'routeDelete' => 'admin.banner1.delete'],
                ['field' => 'banner2', 'title' => 'Banner Principal 02', 'category' => 'Home', 'file' => $banners['banner2'] ?? null, 'routeUpload' => 'admin.banner2.upload', 'routeDelete' => 'admin.banner2.delete'],
                ['field' => 'banner3', 'title' => 'Banner Principal 03', 'category' => 'Home', 'file' => $banners['banner3'] ?? null, 'routeUpload' => 'admin.banner3.upload', 'routeDelete' => 'admin.banner3.delete'],
                ['field' => 'banner4', 'title' => 'Banner Principal 04', 'category' => 'Home', 'file' => $banners['banner4'] ?? null, 'routeUpload' => 'admin.banner4.upload', 'routeDelete' => 'admin.banner4.delete'],
                ['field' => 'banner5', 'title' => 'Banner Principal 05', 'category' => 'Home', 'file' => $banners['banner5'] ?? null, 'routeUpload' => 'admin.banner5.upload', 'routeDelete' => 'admin.banner5.delete'],
                ['field' => 'banner6', 'title' => 'Banner Principal 06', 'category' => 'Home', 'file' => $banners['banner6'] ?? null, 'routeUpload' => 'admin.banner6.upload', 'routeDelete' => 'admin.banner6.delete'],
                ['field' => 'banner7', 'title' => 'Banner Principal 07', 'category' => 'Home', 'file' => $banners['banner7'] ?? null, 'routeUpload' => 'admin.banner7.upload', 'routeDelete' => 'admin.banner7.delete'],
                ['field' => 'banner8', 'title' => 'Banner Principal 08', 'category' => 'Home', 'file' => $banners['banner8'] ?? null, 'routeUpload' => 'admin.banner8.upload', 'routeDelete' => 'admin.banner8.delete'],
                ['field' => 'banner9', 'title' => 'Banner Principal 09', 'category' => 'Home', 'file' => $banners['banner9'] ?? null, 'routeUpload' => 'admin.banner9.upload', 'routeDelete' => 'admin.banner9.delete'],
                ['field' => 'banner10', 'title' => 'Banner Principal 10', 'category' => 'Home', 'file' => $banners['banner10'] ?? null, 'routeUpload' => 'admin.banner10.upload', 'routeDelete' => 'admin.banner10.delete'],
            ];
        @endphp

        @foreach($images as $img)
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="banner-admin-card shadow-sm h-100">
                    <div class="card-top-info p-3">
                        <span class="category-tag">{{ $img['category'] }}</span>
                        <h6 class="fw-bold m-0 mt-1">{{ $img['title'] }}</h6>
                    </div>

                    <div class="preview-container">
                        @if ($img['file'])
                            <img src="{{ asset('storage/uploads/' . $img['file']) }}" class="banner-preview-img">
                            <div class="preview-overlay">
                                <form action="{{ route($img['routeDelete']) }}" method="POST" onsubmit="return confirm('¿Eliminar esta imagen?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm rounded-pill px-3">
                                        <i class="fas fa-trash-alt me-1"></i> Eliminar
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="no-image-placeholder">
                                <i class="fas fa-image fa-2x mb-2 text-muted"></i>
                                <span class="text-muted small">Sin imagen</span>
                            </div>
                        @endif
                    </div>

                    <div class="card-footer-upload p-3 bg-white border-top">
                        <form action="{{ route($img['routeUpload']) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="upload-wrapper">
                                <input type="file" class="form-control form-control-sm mb-2 custom-file-input" name="{{ $img['field'] }}" required>
                                <button type="submit" class="btn btn-dark btn-sm w-100 fw-bold">
                                    <i class="fas fa-cloud-upload-alt me-1"></i> SUBIR ARCHIVO
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
<style>
    /* Container Background */
.admin-banners-wrapper {
    background-color: #f8f9fa;
    min-height: 100vh;
}

/* Banner Card Style */
.banner-admin-card {
    background: #fff;
    border: 1px solid #edf2f9;
    border-radius: 12px;
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.banner-admin-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important;
}

/* Category Tag */
.category-tag {
    font-size: 0.6rem;
    font-weight: 800;
    text-transform: uppercase;
    color: #6e84a3;
    letter-spacing: 0.5px;
}

/* Preview Area */
.preview-container {
    position: relative;
    height: 160px;
    background: #f1f4f8;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.banner-preview-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 10px;
}

.no-image-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
}

/* Overlay no Hover */
.preview-overlay {
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.preview-container:hover .preview-overlay {
    opacity: 1;
}

/* Custom File Input */
.custom-file-input {
    font-size: 0.75rem;
    border-radius: 6px;
    border: 1px dashed #ced4da;
}

.custom-file-input:focus {
    box-shadow: none;
    border-color: #000;
}

/* Alertas Customizadas */
.alert-sax-success {
    background-color: #d1e7dd;
    border-left: 5px solid #0f5132;
    color: #0f5132;
    font-size: 0.9rem;
}

.alert-sax-danger {
    background-color: #f8d7da;
    border-left: 5px solid #842029;
    color: #842029;
    font-size: 0.9rem;
}

.bg-soft-dark {
    background-color: #f1f4f8;
}
</style>