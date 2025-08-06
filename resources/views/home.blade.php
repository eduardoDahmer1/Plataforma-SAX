@extends('layout.layout')

@section('content')
<div class="container">

    <h2>Bem-vindo à Página Inicial</h2>
    <p>Esta é a página de uploads. Aqui você pode ver os arquivos que foram carregados.</p>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Formulário de busca -->
    <form action="{{ url('/') }}" method="GET" class="mb-4">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Buscar por título ou descrição"
                value="{{ request('search') }}">
            <button class="btn btn-primary" type="submit">Buscar</button>
        </div>
    </form>

    <h4 class="mt-4">Arquivos e Produtos Recentes:</h4>

    <div class="row">
        @foreach($items as $item)
        <div class="col-6 col-md-4 col-lg-3 mb-4">
            <div class="card h-100">

                {{-- IMAGEM NO TOPO --}}
                @php
                $photoPath = null;
                $hasPhoto = property_exists($item, 'photo') && $item->photo;

                if ($hasPhoto && Storage::disk('public')->exists($item->photo)) {
                    $photoPath = Storage::url($item->photo);
                } elseif (!empty($noimage) && Storage::disk('public')->exists('uploads/' . $noimage)) {
                    $photoPath = asset('storage/uploads/' . $noimage);
                } else {
                    $photoPath = asset('storage/uploads/noimage.webp');
                }
                @endphp

                <img src="{{ $photoPath }}" class="card-img-top img-fluid" alt="Imagem"
                    style="max-height: 200px; object-fit: cover;">

                <div class="card-body">
                    @if($item->type === 'upload')
                    <h5 class="card-title">{{ $item->title ?? 'Sem título' }}</h5>
                    <p class="card-text">{{ $item->description ?? 'Sem descrição' }}</p>
                    <a href="{{ route('uploads.show', $item->id) }}" class="btn btn-sm btn-info">Ver Detalhes</a>
                    @elseif($item->type === 'product')
                    <h5 class="card-title">
                        <a href="{{ route('produto.show', $item->id) }}">
                            {{ $item->title ?? 'Sem nome' }}
                        </a>
                    </h5>
                    <p class="card-text">
                        <strong>SKU:</strong> {{ $item->description ?? 'Sem SKU' }}<br>
                        <strong>Preço:</strong> R$ {{ number_format($item->price, 2, ',', '.') }}<br>
                        <small>ID: {{ $item->id }}</small>
                    </p>

                    <a href="{{ route('produto.show', $item->id) }}" class="btn btn-sm btn-info mb-2 custom-btn">Ver Detalhes</a>

                    @auth
                        @if(in_array(auth()->user()->user_type, [0, 1, 2]))
                            <form action="{{ route('cart.add') }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $item->id }}">
                                <button type="submit" class="btn btn-sm custom-btn mb-2">+ 🛒</button>
                            </form>

                            <form action="{{ route('checkout.index') }}" method="GET" class="d-inline">
                                <input type="hidden" name="product_id" value="{{ $item->id }}">
                                <button type="submit" class="btn btn-sm custom-btn mb-2">Comprar Agora 🛒</button>
                            </form>
                        @endif
                    @else
                        <a href="#" class="btn btn-sm btn-warning mb-2" data-bs-toggle="modal" data-bs-target="#loginModal">Login para Comprar</a>
                    @endauth

                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Link de paginação -->
    <div class="d-flex justify-content-center mt-4">
        {{ $items->links('pagination::bootstrap-4') }}
    </div>

</div>
@endsection
