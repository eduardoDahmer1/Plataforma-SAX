<div class="col-xl-3 col-lg-4 col-md-6" id="card-{{ $img['field'] }}">
    <div class="banner-admin-card shadow-sm h-100">
        <div class="card-top-info p-3 d-flex justify-content-between align-items-start">
            <div>
                <span class="category-tag">{{ $img['category'] }}</span>
                <h6 class="fw-bold m-0 mt-1 text-truncate" style="max-width: 150px;">{{ $img['title'] }}</h6>
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
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.upload-form').forEach(form => {
    form.onsubmit = async (e) => {
        e.preventDefault();
        const card = form.closest('.banner-admin-card');
        const btn = form.querySelector('.btn-submit');
        const img = card.querySelector('.banner-preview-img');
        const empty = card.querySelector('.empty-state');
        const delBtn = card.querySelector('.btn-delete');
        
        btn.disabled = true;
        const res = await fetch(form.action, { method: 'POST', body: new FormData(form) });
        const data = await res.json();
        
        if (data.success) {
            img.src = data.url;
            img.style.display = 'block';
            empty.style.display = 'none';
            delBtn.style.display = 'block';
        }
        btn.disabled = false;
    };
});

document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.onclick = async () => {
        if (!confirm('{{ __('messages.confirmar_exclusao_imagem') }}')) return;
        const form = btn.closest('.delete-form');
        const card = form.closest('.banner-admin-card');
        const img = card.querySelector('.banner-preview-img');
        const empty = card.querySelector('.empty-state');
        
        const res = await fetch(form.action, { method: 'POST', body: new FormData(form) });
        const data = await res.json();
        
        if (data.success) {
            img.style.display = 'none';
            empty.style.display = 'block';
            btn.style.display = 'none';
        }
    };
});
</script>