@extends('layout.admin')

@section('content')
    <h1>Editar Blog</h1>
    <form action="{{ route('admin.blogs.update', $blog) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.blogs._form')
    </form>
@endsection
