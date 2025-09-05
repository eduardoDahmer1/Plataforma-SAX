@extends('layout.admin')

@section('content')
<div class="container py-4">
    <h1>Banner/Logos</h1>

    {{-- Alertas --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">

        @php
        $images = [
            ['field' => 'header_image', 'title' => 'Logo Header', 'btnClass' => 'info', 'file' => $webpImage, 'routeUpload' => 'admin.header.upload', 'routeDelete' => 'admin.header.delete'],
            ['field' => 'noimage', 'title' => 'Noimage', 'btnClass' => 'info', 'file' => $noimage, 'routeUpload' => 'admin.noimage.upload', 'routeDelete' => 'admin.noimage.delete'],
            ['field' => 'banner1', 'title' => 'Banner 1', 'btnClass' => 'info', 'file' => $banner1, 'routeUpload' => 'admin.banner1.upload', 'routeDelete' => 'admin.banner1.delete'],
            ['field' => 'banner2', 'title' => 'Banner 2', 'btnClass' => 'info', 'file' => $banner2, 'routeUpload' => 'admin.banner2.upload', 'routeDelete' => 'admin.banner2.delete'],
            ['field' => 'banner3', 'title' => 'Banner 3', 'btnClass' => 'info', 'file' => $banner3, 'routeUpload' => 'admin.banner3.upload', 'routeDelete' => 'admin.banner3.delete'],
            ['field' => 'banner4', 'title' => 'Banner 4', 'btnClass' => 'info', 'file' => $banner4, 'routeUpload' => 'admin.banner4.upload', 'routeDelete' => 'admin.banner4.delete'],
            ['field' => 'banner5', 'title' => 'Banner 5', 'btnClass' => 'info', 'file' => $banner5, 'routeUpload' => 'admin.banner5.upload', 'routeDelete' => 'admin.banner5.delete'],
            ['field' => 'banner6', 'title' => 'Banner 6', 'btnClass' => 'info', 'file' => $banner6, 'routeUpload' => 'admin.banner6.upload', 'routeDelete' => 'admin.banner6.delete'],
            ['field' => 'banner7', 'title' => 'Banner 7', 'btnClass' => 'info', 'file' => $banner7, 'routeUpload' => 'admin.banner7.upload', 'routeDelete' => 'admin.banner7.delete'],
            ['field' => 'banner8', 'title' => 'Banner 8', 'btnClass' => 'info', 'file' => $banner8, 'routeUpload' => 'admin.banner8.upload', 'routeDelete' => 'admin.banner8.delete'],
            ['field' => 'banner9', 'title' => 'Banner 9', 'btnClass' => 'info', 'file' => $banner9, 'routeUpload' => 'admin.banner9.upload', 'routeDelete' => 'admin.banner9.delete'],
            ['field' => 'banner10', 'title' => 'Banner 10', 'btnClass' => 'info', 'file' => $banner10, 'routeUpload' => 'admin.banner10.upload', 'routeDelete' => 'admin.banner10.delete'],
        ];
    @endphp

        @foreach($images as $img)
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-{{ $img['btnClass'] }} text-white">
                        <h5 class="mb-0">{{ $img['title'] }}</h5>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <form action="{{ route($img['routeUpload']) }}" method="POST" enctype="multipart/form-data" class="mb-3">
                            @csrf
                            <input type="file" class="form-control mb-2" name="{{ $img['field'] }}" required>
                            <button type="submit" class="btn btn-{{ $img['btnClass'] }} w-100">Enviar {{ $img['title'] }}</button>
                        </form>

                        @if ($img['file'])
                            <div class="text-center mt-auto">
                                <img src="{{ asset('storage/uploads/' . $img['file']) }}" class="img-fluid mb-2 rounded shadow-sm" style="width: 100%; height: 200px; object-fit: scale-down;">
                                <form action="{{ route($img['routeDelete']) }}" method="POST" onsubmit="return confirm('Deseja excluir {{ $img['title'] }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger w-100">Excluir {{ $img['title'] }}</button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach

    </div>
</div>
@endsection
