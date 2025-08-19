@extends('layout.admin')

@section('content')
    <form action="{{ route('admin.blogs.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('admin.blogs._form')
    </form>
@endsection
