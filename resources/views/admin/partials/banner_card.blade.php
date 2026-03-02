<div class="col-xl-3 col-lg-4 col-md-6">
    <div class="banner-admin-card shadow-sm h-100">
        <div class="card-top-info p-3 d-flex justify-content-between align-items-start">
            <div>
                <span class="category-tag">{{ $img['category'] }}</span>
                <h6 class="fw-bold m-0 mt-1 text-truncate" style="max-width: 150px;">{{ $img['title'] }}</h6>
            </div>
            @if($img['file'])
                <div class="preview-overlay">
                    <form action="{{ route($img['routeDelete']) }}" method="POST" onsubmit="return confirm('¿Eliminar esta imagen?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm rounded-circle shadow">
                            <i class="fas fa-times"></i>
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <div class="preview-container">
            @if ($img['file'])
                <img src="{{ asset('storage/uploads/' . $img['file']) }}" class="banner-preview-img">
            @else
                <div class="text-center">
                    <div class="mb-2 text-muted opacity-25">
                        <i class="fas fa-image fa-3x"></i>
                    </div>
                    <span class="text-muted small fw-medium">Vacío</span>
                </div>
            @endif
        </div>

        <div class="card-footer-upload p-3 bg-white border-top">
            <form action="{{ route($img['routeUpload']) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="upload-wrapper mb-2">
                    <input type="file" class="custom-file-input" name="{{ $img['field'] }}" required>
                    <div class="btn-upload-label">
                        <i class="fas fa-cloud-upload-alt me-1"></i> Seleccionar
                    </div>
                </div>
                <button type="submit" class="btn btn-dark btn-sm w-100 fw-bold btn-submit-action py-2">
                    Actualizar
                </button>
            </form>
        </div>
    </div>
</div>