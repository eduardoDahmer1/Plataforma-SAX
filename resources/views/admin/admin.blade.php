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
                ['field' => 'logo_cafe_bistro', 'title' => 'Logo SAX Café & Bistrô', 'category' => 'Identidad', 'file' => $logoCafeBistro ?? null, 'routeUpload' => 'admin.logocafebistro.upload', 'routeDelete' => 'admin.logocafebistro.delete'],
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


@endsection

<style>

</style>