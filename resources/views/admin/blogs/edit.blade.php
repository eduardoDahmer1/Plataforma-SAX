@extends('layout.admin')

@push('styles')
<link href="{{ asset('css/admin-content.css') }}?v={{ filemtime(public_path('css/admin-content.css')) }}" rel="stylesheet">
@endpush

@section('content')
<div class="content-admin content-blog content-editor">
<x-admin.card>
    <form id="blogForm" action="{{ route('admin.blogs.update', $blog) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.blogs._form')
    </form>
</x-admin.card>
</div>
@endsection
