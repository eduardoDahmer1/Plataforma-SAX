@extends('layout.admin')

@section('content')
<style>
    /* Área da imagem dentro do card */
    .category-image-wrapper {
        width: 100%;
        height: 180px; /* tamanho fixo — pode ajustar */
        overflow: hidden;
        border-radius: 10px;
        background: #f0f0f0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .category-image-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover; /* garante padrão e sem deformar */
        transition: transform .3s ease;
    }

    .category-image-wrapper img:hover {
        transform: scale(1.05);
    }

    .card {
        border: none;
        border-radius: 14px;
        overflow: hidden;
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .card:hover {
        transform: translateY(-4px);
        box-shadow: 0px 10px 28px rgba(0, 0, 0, .15);
    }

    /* Botões */
    .card .btn {
        border-radius: 8px;
    }

    /* Responsividade fina */
    @media (max-width: 576px) {
        .category-image-wrapper {
            height: 150px;
        }
    }
</style>


<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <h1 class="m-0">Gerenciar Categorias</h1>

        <a href="{{ route('admin.blog-categories.create') }}" class="btn btn-success">
            <i class="fa fa-plus me-1"></i> Nova Categoria
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($categories->count())

    <div class="row g-4">
        @foreach($categories as $cat)
            <div class="col-sm-6 col-lg-4">

                <div class="card shadow-sm h-100">

                    {{-- Imagem com tamanho padronizado --}}
                    <div class="category-image-wrapper">
                        @if($cat->banner && Storage::disk('public')->exists($cat->banner))
                            <img src="{{ Storage::url($cat->banner) }}" alt="{{ $cat->name }}">
                        @else
                            <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Sem imagem">
                        @endif
                    </div>

                    <div class="card-body d-flex flex-column">

                        <h5 class="card-title mt-2">{{ $cat->name }}</h5>

                        <div class="mt-auto d-flex flex-column flex-sm-row gap-2">

                            <a href="{{ route('admin.blog-categories.edit', $cat) }}" 
                               class="btn btn-warning btn-sm flex-fill">
                                <i class="fa fa-edit me-1"></i> Editar
                            </a>

                            <a href="{{ route('admin.blog-categories.show', $cat) }}" 
                               class="btn btn-info btn-sm flex-fill">
                                <i class="fa fa-eye me-1"></i> Ver
                            </a>

                            <form action="{{ route('admin.blog-categories.destroy', $cat) }}" 
                                  method="POST" class="flex-fill m-0"
                                  onsubmit="return confirm('Excluir categoria?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm w-100">
                                    <i class="fa fa-trash me-1"></i> Excluir
                                </button>
                            </form>

                        </div>

                    </div>

                </div>

            </div>
        @endforeach
    </div>

    @else
        <p class="text-muted">Nenhuma categoria encontrada.</p>
    @endif

</div>
@endsection
