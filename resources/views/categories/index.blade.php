@extends('layout.layout')
@section('content')
<div class="container py-4">
    <h1>Categorias</h1>
    <div class="row">
        @foreach ($categories as $category)
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                @if ($category->image)
                    <img src="{{ asset('storage/' . $category->image) }}" class="card-img-top" alt="{{ $category->name }}">
                @endif
                <div class="card-body">
                    <h5>{{ $category->name ?? $category->slug }}</h5>
                    <a href="{{ route('categories.show', $category->id) }}" class="btn btn-primary">Ver detalhes</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    {{ $categories->links() }}
</div>
@endsection
