@extends('layout.admin')

@section('content')
    <div class="sax-admin-container py-2">
        {{-- Header --}}
        <div class="dashboard-header d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="sax-title text-uppercase letter-spacing-2 m-0">Editar Marca</h2>
                <div class="sax-divider-dark"></div>
                <span class="text-muted x-small">ID da Marca: #{{ $brand->id }}</span>
            </div>
            <a href="{{ route('admin.brands.index') }}" class="btn-back-minimal">
                <i class="fas fa-times me-1"></i> CANCELAR
            </a>
        </div>

        <div class="row">
            <div class="col-lg-10 mx-auto">
                <form action="{{ route('admin.brands.update', $brand->id) }}" method="POST" enctype="multipart/form-data"
                    class="sax-form">
                    @csrf
                    @method('PUT')

                    <div class="row g-4">
                        {{-- Informações Textuais --}}
                        <div class="col-md-6">
                            <div class="sax-premium-card p-4 h-100 shadow-sm">
                                <h6 class="sax-label mb-4 text-dark border-bottom pb-2">DATOS DE IDENTIFICACIÓN</h6>

                                <div class="mb-4">
                                    <label for="name" class="sax-form-label">Nombre de la Marca</label>
                                    <input type="text" class="form-control sax-input @error('name') is-invalid @enderror"
                                        id="name" name="name" value="{{ old('name', $brand->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="slug" class="sax-form-label">Slug de Navegación</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0 x-small fw-bold text-muted">/marcas/</span>
                                        <input type="text" class="form-control sax-input @error('slug') is-invalid @enderror"
                                            id="slug" name="slug" value="{{ old('slug', $brand->slug) }}" required>
                                    </div>
                                    @error('slug')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Upload de Arquivos --}}
                        <div class="col-md-6">
                            <div class="sax-premium-card p-4 h-100 shadow-sm">
                                <h6 class="sax-label mb-4 text-dark border-bottom pb-2">ARCHIVOS MULTIMEDIA</h6>

                                {{-- Logotipo --}}
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label for="image" class="sax-form-label m-0">Logotipo Oficial</label>
                                        @if($brand->image)
                                            <span class="badge bg-success-soft text-success x-small">
                                                <i class="fas fa-check-circle"></i> ACTUALMENTE CON LOGO
                                            </span>
                                        @endif
                                    </div>
                                    <div class="asset-upload-zone">
                                        <i class="fas fa-cloud-upload-alt mb-2 opacity-25"></i>
                                        <input type="file" name="image" class="form-control sax-input-file" accept="image/*">
                                        <p class="x-small text-muted mb-0">Haga clic para reemplazar el logo</p>
                                    </div>
                                </div>

                                {{-- Banner Principal --}}
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label for="banner" class="sax-form-label m-0">Banner Promocional</label>
                                        @if($brand->banner)
                                            <span class="badge bg-success-soft text-success x-small">
                                                <i class="fas fa-check-circle"></i> ACTUALMENTE CON BANNER
                                            </span>
                                        @endif
                                    </div>
                                    <div class="asset-upload-zone">
                                        <i class="fas fa-image mb-2 opacity-25"></i>
                                        <input type="file" name="banner" class="form-control sax-input-file" accept="image/*">
                                        <p class="x-small text-muted mb-0">Haga clic para reemplazar el banner</p>
                                    </div>
                                </div>

                                {{-- Internal Banner --}}
                                <div class="mb-0">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label for="internal_banner" class="sax-form-label m-0">Banner Interno</label>
                                        @if($brand->internal_banner)
                                            <span class="badge bg-success-soft text-success x-small">
                                                <i class="fas fa-check-circle"></i> ACTUALMENTE CON BANNER INT.
                                            </span>
                                        @endif
                                    </div>
                                    <div class="asset-upload-zone" style="border-color: #d1d5db; background: #f3f4f6;">
                                        <i class="fas fa-ad mb-2 opacity-25"></i>
                                        <input type="file" name="internal_banner" class="form-control sax-input-file" accept="image/*">
                                        <p class="x-small text-muted mb-0">Haga clic para reemplazar banner interno</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Ações Inferiores --}}
                    <div class="mt-5 pt-4 border-top d-flex justify-content-between align-items-center">
                        <a href="{{ route('admin.index') }}" class="btn btn-link text-dark text-decoration-none x-small fw-bold">
                            <i class="fas fa-arrow-left me-1"></i> DASHBOARD
                        </a>
                        <div class="d-flex gap-2">
                            {{-- Botão para Resetar/Limpar campos (Opcional) --}}
                            <a href="{{ route('admin.brands.index') }}" class="btn btn-light rounded-pill px-4 text-muted x-small fw-bold">DESCARTAR</a>
                            <button type="submit" class="btn btn-dark rounded-pill px-5 fw-bold letter-spacing-1">
                                GUARDAR CAMBIOS <i class="fas fa-check-circle ms-2 text-warning"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
