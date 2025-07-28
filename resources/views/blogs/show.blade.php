@extends('layout.layout')
@section('content')
<div class="container py-4">
    <a href="{{ route('blogs.index') }}" class="btn btn-primary mb-3">Ver Blogs</a>
    <h1>{{ $blog->title }}</h1>
    <h6 class="text-muted">{{ $blog->subtitle }}</h6>
    @if ($blog->image)
        <img src="{{ asset('storage/' . $blog->image) }}" class="img-fluid my-3">
    @endif
    <div>{!! $blog->content !!}</div>
</div>
@endsection
