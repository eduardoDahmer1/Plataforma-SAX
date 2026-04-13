@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="{{ __('messages.cupons_desconto_titulo') }}"
        description="{{ __('messages.gestao_incentivos_desc') }}">
        <x-slot:actions>
            <a href="{{ route('admin.cupons.create') }}" class="btn btn-dark btn-sm rounded-0 px-4 text-uppercase fw-bold x-small tracking-wider">
                <i class="fas fa-plus me-2"></i> {{ __('messages.novo_cupon_btn') }}
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Alertas --}}
    @if (session('error') || session('success'))
        <div class="alert {{ session('error') ? 'alert-danger' : 'alert-dark' }} border-0 rounded-0 x-small fw-bold text-uppercase py-3 mb-4 shadow-sm">
            <i class="fas {{ session('error') ? 'fa-exclamation-circle' : 'fa-check-circle' }} me-2"></i>
            {{ session('error') ?? session('success') }}
        </div>
    @endif

    {{-- Grid de Cupons --}}
    <div class="row g-4">
        @forelse($cupons as $cupon)
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card border rounded-0 shadow-none sax-coupon-card">
                    <div class="card-body p-0">
                        {{-- Cabeçalho do Ticket --}}
                        <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                            <span class="x-small fw-800 text-uppercase text-secondary tracking-wider">{{ __('messages.codigo_label_mini') }}</span>
                            <span class="badge {{ $cupon->tipo === 'percentual' ? 'bg-primary' : 'bg-dark' }} rounded-0 x-small">
                                {{ $cupon->tipo === 'percentual' ? '%' : '$' }}
                            </span>
                        </div>

                        {{-- Corpo do Ticket --}}
                        <div class="p-4 text-center border-bottom border-dashed">
                            <h2 class="h3 fw-900 tracking-tighter mb-1 text-uppercase">{{ $cupon->codigo }}</h2>
                            <span class="h5 fw-light text-dark font-monospace">
                                {{ $cupon->tipo === 'percentual' ? $cupon->montante . '%' : number_format($cupon->montante, 2) }}
                            </span>
                        </div>

                        {{-- Detalhes Técnicos --}}
                        <div class="p-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="sax-label-mini">{{ __('messages.modelo_label_mini') }}</span>
                                <span class="x-small fw-bold text-dark text-uppercase">{{ $cupon->modelo ?? __('messages.universal_text') }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="sax-label-mini">{{ __('messages.categoria_label_mini') }}</span>
                                <span class="x-small fw-bold text-dark text-uppercase">{{ $cupon->category->name ?? __('messages.todas_text') }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="sax-label-mini">{{ __('messages.marca_label_mini') }}</span>
                                <span class="x-small fw-bold text-dark text-uppercase">{{ $cupon->brand->name ?? __('messages.todas_text') }}</span>
                            </div>
                        </div>

                        {{-- Ações --}}
                        <div class="p-3 bg-light d-flex gap-3 border-top">
                            <a href="{{ route('admin.cupons.edit', $cupon) }}" class="text-dark text-decoration-none x-small fw-bold tracking-tighter hover-underline">
                                {{ __('messages.editar_btn_mini') }}
                            </a>
                            <form action="{{ route('admin.cupons.destroy', $cupon) }}" method="POST"
                                onsubmit="return confirm('{{ __('messages.confirmar_eliminar_cupon') }}')" class="ms-auto m-0">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-clean text-danger x-small fw-bold tracking-tighter">
                                    {{ __('messages.eliminar_btn_mini') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 py-5 text-center border border-dashed">
                <p class="text-muted x-small text-uppercase tracking-wider mb-0 italic">{{ __('messages.sem_cupons_aviso') }}</p>
            </div>
        @endforelse
    </div>

    {{-- Paginação --}}
    <div class="mt-5 d-flex justify-content-center">
        {{ $cupons->links() }}
    </div>
</x-admin.card>
@endsection