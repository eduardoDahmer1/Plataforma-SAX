@extends('layout.layout')
@section('content')
<div class="container py-4">
    <h1>Marcas</h1>
    <div class="row">
        @foreach ($brands as $brand)
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                @if ($brand->image)
                    <img src="{{ asset('storage/' . $brand->image) }}" class="card-img-top" alt="{{ $brand->name }}">
                @endif
                <div class="card-body">
                    <h5 class="card-title">{{ $brand->name }}</h5>
                    <a href="{{ route('brands.show', $brand->id) }}" class="btn btn-primary">Ver detalhes</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    {{ $brands->links() }}
</div>
@endsection
