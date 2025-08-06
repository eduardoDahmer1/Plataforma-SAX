@extends('layout.admin')

@section('content')
<div class="container mt-4">
    <h2>Editar Categoria</h2>

    <form action="{{ route('admin.categories.update', $category) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="name" class="form-label">Nome</label>
            <input type="text" class="form-control" name="name" value="{{ $category->name }}" required>
        </div>

        <div class="mb-3">
            <label for="slug" class="form-label">Slug</label>
            <input type="text" class="form-control" name="slug" value="{{ $category->slug }}" required>
        </div>

        <div class="mb-3">
            <label for="photo" class="form-label">Foto (Logo)</label>
            <input type="file" class="form-control" name="photo" accept="image/*">
            @if ($category->photo)
                <img src="{{ asset('storage/' . $category->photo) }}" alt="Logo" style="max-width:150px; margin-top:10px;">
                <button type="button" class="btn btn-danger mt-2" id="delete-photo" data-id="{{ $category->id }}">Excluir Foto</button>
            @endif
        </div>

        <div class="mb-3">
            <label for="banner" class="form-label">Banner</label>
            <input type="file" class="form-control" name="banner" accept="image/*">
            @if ($category->banner)
                <img src="{{ asset('storage/' . $category->banner) }}" alt="Banner" style="max-width:150px; margin-top:10px;">
                <button type="button" class="btn btn-danger mt-2" id="delete-banner" data-id="{{ $category->id }}">Excluir Banner</button>
            @endif
        </div>

        <button type="submit" class="btn btn-primary">Atualizar</button>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Cancelar</a>
        <a href="{{ route('admin.index') }}" class="btn btn-secondary">Admin</a>
    </form>
</div>

<script>
document.getElementById('delete-photo')?.addEventListener('click', function() {
    if (confirm('Você tem certeza que deseja excluir esta foto?')) {
        // Garante que a URL use HTTPS
        const url = `https://${window.location.host}/admin/categories/delete-photo/${this.dataset.id}`;
        
        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }).then(response => {
            if (response.ok) {
                alert('Foto excluída com sucesso!');
                location.reload(); // Recarrega a página para refletir a exclusão
            } else {
                alert('Erro ao excluir a foto.');
            }
        }).catch(error => {
            console.error('Erro na requisição:', error);
        });
    }
});

document.getElementById('delete-banner')?.addEventListener('click', function() {
    if (confirm('Você tem certeza que deseja excluir este banner?')) {
        // Garante que a URL use HTTPS
        const url = `https://${window.location.host}/admin/categories/delete-banner/${this.dataset.id}`;
        
        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }).then(response => {
            if (response.ok) {
                alert('Banner excluído com sucesso!');
                location.reload(); // Recarrega a página para refletir a exclusão
            } else {
                alert('Erro ao excluir o banner.');
            }
        }).catch(error => {
            console.error('Erro na requisição:', error);
        });
    }
});

</script>
@endsection
