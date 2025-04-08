<div>
    <a href="{{ route('pages.contato') }}" class="btn btn-link">Contato</a>
    <a href="{{ route('pages.sobre') }}" class="btn btn-link">Sobre Nós</a>
    <a href="{{ route('pages.home') }}" class="btn btn-link">Home</a>

    <form action="{{ route('uploads.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="text" name="title" placeholder="Título" class="form-control mb-2">
        <textarea name="description" placeholder="Descrição" class="form-control mb-2"></textarea>
        <input type="file" name="file" class="form-control mb-2">
        <button type="submit" class="btn btn-primary">Enviar</button>
    </form>
</div>
