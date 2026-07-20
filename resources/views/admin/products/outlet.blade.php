@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="Gestão de outlet"
        description="Retire ou devolva grandes lotes de produtos ao e-commerce usando seus SKUs.">
        <x-slot:actions>
            <a href="{{ route('admin.products.index', ['outlet_filter' => 'outlet']) }}" class="btn btn-outline-dark btn-sax-lg px-4 text-uppercase fw-bold letter-spacing-1">
                <i class="fa fa-list me-2"></i> Ver produtos outlet
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.alert />

    @if (session('missing_skus') && count(session('missing_skus')))
        <div class="alert alert-warning mb-4">
            <strong>{{ count(session('missing_skus')) }} SKU(s) não encontrado(s):</strong>
            <div class="mt-2 small text-break">{{ implode(', ', session('missing_skus')) }}</div>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-xl-5">
                    <form method="POST" action="{{ route('admin.products.outlet.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="skus" class="form-label fw-bold text-uppercase letter-spacing-1 small">Lista de SKUs</label>
                            <textarea id="skus" name="skus" rows="15"
                                class="form-control outlet-skus @error('skus') is-invalid @enderror"
                                placeholder="Cole os SKUs aqui, um por linha. Também aceitamos vírgula, ponto e vírgula ou espaço."
                                required>{{ old('skus') }}</textarea>
                            @error('skus')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text mt-2">Até 5.000 SKUs por operação. Duplicados serão ignorados.</div>
                        </div>

                        <fieldset>
                            <legend class="form-label fw-bold text-uppercase letter-spacing-1 small">O que deseja fazer?</legend>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="outlet-action-option outlet-action-danger">
                                        <input type="radio" name="action" value="outlet" @checked(old('action', 'outlet') === 'outlet') required>
                                        <span class="outlet-action-icon"><i class="fa fa-box-open"></i></span>
                                        <span><strong>Enviar para outlet</strong><small>Desativa e bloqueia a venda no site.</small></span>
                                    </label>
                                </div>
                                <div class="col-md-6">
                                    <label class="outlet-action-option outlet-action-success">
                                        <input type="radio" name="action" value="restore" @checked(old('action') === 'restore') required>
                                        <span class="outlet-action-icon"><i class="fa fa-rotate-left"></i></span>
                                        <span><strong>Voltar ao normal</strong><small>Restaura o status anterior do produto.</small></span>
                                    </label>
                                </div>
                            </div>
                            @error('action')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                        </fieldset>

                        <div class="d-flex justify-content-end mt-5">
                            <button type="submit" class="btn btn-dark px-5 py-3 text-uppercase fw-bold letter-spacing-1"
                                onclick="return confirm('Confirma a alteração em lote para todos os SKUs informados?')">
                                Aplicar aos produtos
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 outlet-summary">
                <span>Produtos atualmente no outlet</span>
                <strong>{{ number_format($outletCount, 0, ',', '.') }}</strong>
            </div>
            <div class="alert alert-light border p-4">
                <h6 class="fw-bold"><i class="fa fa-shield-alt me-2"></i>Bloqueio de venda</h6>
                <p class="small text-muted mb-0">Produtos outlet não aparecem no catálogo ou na busca, não podem ser adicionados ao carrinho e são recusados novamente no checkout.</p>
            </div>
        </div>
    </div>
</x-admin.card>
@endsection

@push('styles')
<style>
    .outlet-skus { min-height:300px; padding:18px; border-color:#d9dee7; border-radius:12px; font-family:ui-monospace, SFMono-Regular, Menlo, monospace; font-size:.88rem; line-height:1.65; }
    .outlet-action-option { display:flex; align-items:center; gap:14px; min-height:96px; padding:17px; border:1px solid #dfe3ea; border-radius:12px; cursor:pointer; transition:.18s ease; }
    .outlet-action-option:has(input:checked) { border-color:#111; box-shadow:0 0 0 2px rgba(17,17,17,.08); }
    .outlet-action-option input { position:absolute; opacity:0; }
    .outlet-action-option strong, .outlet-action-option small { display:block; }
    .outlet-action-option small { margin-top:4px; color:#7a8494; font-size:.75rem; }
    .outlet-action-icon { display:grid; flex:0 0 42px; width:42px; height:42px; place-items:center; border-radius:50%; }
    .outlet-action-danger .outlet-action-icon { color:#b42318; background:#fee4e2; }
    .outlet-action-success .outlet-action-icon { color:#067647; background:#dcfae6; }
    .outlet-summary span { color:#667085; font-size:.75rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; }
    .outlet-summary strong { margin-top:8px; font-size:2.25rem; line-height:1; }
</style>
@endpush
