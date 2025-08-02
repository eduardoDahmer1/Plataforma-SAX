@extends('layout.layout')
@section('content')
<div class="container py-4">
    <h1>{{ $category->name }}</h1>

    @if ($category->image)
        <img src="{{ asset('storage/' . $category->image) }}" class="img-fluid mb-3" alt="{{ $category->name }}">
    @endif

    <p><strong>ID:</strong> {{ $category->id }}</p>
    <p><strong>Slug:</strong> {{ $category->slug }}</p>

    <a href="{{ route('categories.index') }}" class="btn btn-secondary mt-3">Voltar</a>
</div>
@endsection
