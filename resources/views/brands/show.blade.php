@extends('layout.layout')
@section('content')
<div class="container py-4">
    <h1>{{ $brand->name }}</h1>

    @if ($brand->image)
        <img src="{{ asset('storage/' . $brand->image) }}" class="img-fluid mb-3" alt="{{ $brand->name }}">
    @endif

    <p><strong>ID:</strong> {{ $brand->id }}</p>
    <p><strong>Slug:</strong> {{ $brand->slug }}</p>

    <a href="{{ route('brands.index') }}" class="btn btn-secondary mt-3">Voltar</a>
</div>
@endsection
