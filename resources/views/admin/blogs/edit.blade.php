@extends('layout.admin')

@section('content')
    <form action="{{ route('admin.blogs.update', $blog) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.blogs._form')
    </form>
@endsection
