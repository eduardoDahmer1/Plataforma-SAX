@extends('layout.dashboard')

@section('content')
<div class="sax-wishlist-wrapper">
    {{-- Cabeçalho --}}
    <div class="dashboard-header mb-5">
        <h2 class="sax-title text-uppercase letter-spacing-2">{{ __('messages.wishlist_titulo') }}</h2>
        <div class="sax-divider-dark"></div>
    </div>

    @if (session('success'))
        <div class="alert alert-dark border-0 rounded-0 x-small letter-spacing-1 mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if ($favoriteProducts->isEmpty())
        <div class="empty-wishlist text-center py-5 border">
            <i class="far fa-heart fa-3x mb-3 opacity-25"></i>
            <p class="text-muted text-uppercase letter-spacing-1 small">{{ __('messages.lista_vazia') }}</p>
            <a href="{{ route('home') }}" class="btn btn-dark rounded-0 px-5 mt-3 x-small fw-bold letter-spacing-2">
                {{ __('messages.explorar_produtos') }}
            </a>
        </div>
    @else
        {{-- Grid Ajustado --}}
        <div class="row g-2">
            @foreach ($favoriteProducts as $product)

                <div class="col-6 col-md-4 col-lg-3 mb-3" id="product-{{ $product->id }}">
                    <div class="card h-100 border-0 rounded-0 jw-product-card">

                        {{-- Área da Imagem --}}
                        <div class="jw-img-container position-relative">
                            <a href="{{ route('produto.show', $product->slug ?? $product->id) }}" class="w-100 h-100">
                                <img src="{{ $product->photo ? asset('storage/' . $product->photo) : asset('storage/uploads/noimage.webp') }}"
                                    class="card-img-top img-fluid rounded-0" alt="{{ $product->external_name }}">
                            </a>
                            {{-- Botão de Remover (X) --}}
                            <div class="position-absolute top-0 end-0 p-3">
                                <form action="{{ route('user.preferences.toggle') }}" method="POST" class="remove-fav-form">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    {{-- Adicionei a classe confirm-remover --}}
                                    <button type="submit" class="btn-remove-jw confirm-remover" title="{{ __('messages.remover_wishlist') }}">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="1.2">
                                            <path d="M18 6L6 18M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="card-body px-2 py-3 d-flex flex-column">
                            <div class="sax-brand fw-bold text-uppercase mb-1">
                                {{ $product->brand->name ?? 'BRAND NAME' }}
                            </div>

                            <div class="sax-product-name text-muted mb-3">
                                {{ $product->name ?? $product->external_name }}
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <div class="sax-price fw-bold text-dark">
                                        {{ isset($product->price) ? currency_format($product->price, 2, ',', '.') : '0,00' }}
                                </div>
                                <div class="sax-sku text-muted">
                                    {{ __('messages.sku') }}: {{ $product->sku ?? __('messages.nao_disponivel') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-center mt-5">
            {{ $favoriteProducts->links() }}
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.querySelectorAll('.remove-fav-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Trava o envio automático
            
            const formContainer = this;

            Swal.fire({
                title: 'TEM CERTEZA?',
                text: "Este item será removido da sua lista de desejos.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#000000', // Preto Sax
                cancelButtonColor: '#d33',
                confirmButtonText: 'SIM, REMOVER',
                cancelButtonText: 'NÃO',
                border: 'none',
                borderRadius: '0' // Estilo quadrado Sax
            }).then((result) => {
                if (result.isConfirmed) {
                    formContainer.submit(); // Envia se confirmar
                }
            })
        });
    });
</script>
@endpush
