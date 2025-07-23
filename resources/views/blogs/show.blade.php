@extends('layout.layout')
@section('content')
<div class="container py-4">
    <h1>{{ $blog->title }}</h1>
    <h6 class="text-muted">{{ $blog->subtitle }}</h6>
    @if ($blog->image)
        <img src="{{ asset('storage/' . $blog->image) }}" class="img-fluid my-3">
    @endif
    <div>{!! nl2br(e($blog->content)) !!}</div>
</div>
@endsection
