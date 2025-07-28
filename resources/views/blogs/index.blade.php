@extends('layout.layout')
@section('content')
<div class="container py-4">
    <h1>Blog</h1>
    <div class="row">
        @foreach ($blogs as $blog)
        <div class="col-md-6 mb-4">
            <div class="card">
                @if ($blog->image)
                    <img src="{{ asset('storage/' . $blog->image) }}" class="card-img-top" alt="{{ $blog->title }}">
                @endif
                <div class="card-body">
                    <h5 class="card-title">{{ $blog->title }}</h5>
                    <p class="card-text">{{ $blog->subtitle }}</p>
                    <a href="{{ route('blogs.show', $blog->slug) }}" class="btn btn-primary">Leia mais</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    {{ $blogs->links() }}
</div>
@endsection
