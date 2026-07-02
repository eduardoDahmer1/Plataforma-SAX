<div class="col-xl-3 col-lg-4 col-md-6" id="card-{{ $img['field'] }}">
    <div class="banner-admin-card shadow-sm h-100">
        <div class="card-top-info p-3 d-flex justify-content-between align-items-start">
            <div>
                <span class="category-tag">{{ $img['category'] }}</span>
                <h6 class="fw-bold m-0 mt-1 text-truncate" style="max-width: 150px;">{{ $img['title'] }}</h6>
                @if(!empty($img['linkField']))
                    <a href="{{ $img['link'] ?: '#' }}" target="_blank" rel="noopener noreferrer" class="banner-active-link d-inline-flex align-items-center gap-1 small text-decoration-none mt-1 {{ empty($img['link']) ? 'd-none' : '' }}">
                        <i class="fas fa-link"></i>
                        <span class="text-truncate" style="max-width: 150px;">Link ativo</span>
                    </a>
                @elseif(!empty($img['link']))
                    <a href="{{ $img['link'] }}" target="_blank" rel="noopener noreferrer" class="d-inline-flex align-items-center gap-1 small text-decoration-none mt-1">
                        <i class="fas fa-link"></i>
                        <span class="text-truncate" style="max-width: 150px;">Link ativo</span>
                    </a>
                @endif
            </div>
            <div class="preview-overlay">
                <form action="{{ route($img['routeDelete']) }}" method="POST" class="delete-form">
                    @csrf @method('DELETE')
                    <button type="button" class="btn btn-danger btn-sm rounded-circle shadow btn-delete" {{ !$img['file'] ? 'style=display:none' : '' }}>
                        <i class="fas fa-times"></i>
                    </button>
                </form>
            </div>
        </div>

        <div class="preview-container">
            <img src="{{ $img['file'] ? asset('storage/uploads/' . $img['file']) : '' }}" 
                 class="banner-preview-img" 
                 style="{{ !$img['file'] ? 'display:none' : '' }}">
            <div class="text-center empty-state" style="{{ $img['file'] ? 'display:none' : '' }}">
                <div class="mb-2 text-muted opacity-25">
                    <i class="fas fa-image fa-3x"></i>
                </div>
                <span class="text-muted small fw-medium">{{ __('messages.vazio_label') }}</span>
            </div>
        </div>

        <div class="card-footer-upload p-3 bg-white border-top">
            <form action="{{ route($img['routeUpload']) }}" method="POST" enctype="multipart/form-data" class="upload-form">
                @csrf
                <div class="upload-wrapper mb-2">
                    <input type="file" class="custom-file-input" name="{{ $img['field'] }}" required>
                    <div class="btn-upload-label">
                        <i class="fas fa-cloud-upload-alt me-1"></i> {{ __('messages.selecionar_arquivo_btn') }}
                    </div>
                </div>
                <button type="submit" class="btn btn-dark btn-sm w-100 fw-bold py-2 btn-submit">
                    {{ __('messages.atualizar_btn') }}
                </button>
            </form>

            @if(!empty($img['linkField']))
                <hr class="my-3">
                <form action="{{ route('admin.attributes.update_banner_links') }}" method="POST" class="banner-link-form">
                    @csrf
                    @method('PUT')

                    <label for="{{ $img['linkField'] }}" class="form-label small text-uppercase fw-semibold mb-1">
                        Link deste banner
                    </label>
                    <input
                        type="url"
                        id="{{ $img['linkField'] }}"
                        name="{{ $img['linkField'] }}"
                        class="form-control form-control-sm banner-link-input"
                        placeholder="https://exemplo.com/campanha"
                        value="{{ old($img['linkField'], $img['link'] ?? '') }}"
                    >

                    <button type="submit" class="btn btn-outline-dark btn-sm w-100 mt-2 fw-semibold btn-link-submit">
                        <i class="fas fa-link me-1"></i>Salvar link
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>