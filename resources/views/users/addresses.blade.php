@extends('layout.dashboard')

@section('content')
<div class="dashboard-header mb-4">
    <h2 class="sax-title text-uppercase letter-spacing-2 mb-2">Meus endereços</h2>
    <p class="text-muted">Cadastre os locais de entrega e escolha qual será utilizado como padrão.</p>
    <div class="sax-divider-dark"></div>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="row g-3 mb-4">
    @forelse ($addresses as $address)
        <div class="col-lg-6">
            <div class="card h-100 border rounded-4 p-4">
                <div class="d-flex justify-content-between gap-3">
                    <div>
                        <h5 class="fw-bold mb-2">{{ $address->label }}</h5>
                        <p class="text-muted mb-0">{{ $address->formatted() }}</p>
                    </div>
                    @if ($address->is_default)
                        <span class="badge bg-dark align-self-start">Padrão</span>
                    @endif
                </div>
                <div class="d-flex flex-wrap gap-2 mt-4">
                    @unless ($address->is_default)
                        <form method="POST" action="{{ route('user.addresses.default', $address) }}">
                            @csrf @method('PATCH')
                            <button class="btn btn-dark btn-sm">Definir como padrão</button>
                        </form>
                    @endunless
                    @if ($addresses->count() > 1)
                        <form method="POST" action="{{ route('user.addresses.destroy', $address) }}"
                              onsubmit="return confirm('Remover este endereço?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-outline-danger btn-sm">Remover</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12"><div class="alert alert-light border">Você ainda não possui endereços salvos.</div></div>
    @endforelse
</div>

<div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
    <h4 class="fw-bold mb-4">Cadastrar novo endereço</h4>
    <form method="POST" action="{{ route('user.addresses.store') }}" class="row g-3">
        @csrf
        <div class="col-md-6">
            <label class="sax-label">Identificação</label>
            <input name="label" class="form-control sax-input" value="{{ old('label') }}" placeholder="Casa, Trabalho..." required>
        </div>
        <div class="col-md-6">
            <label class="sax-label">País</label>
            <select name="country" id="address-country" class="form-select sax-input" required>
                <option value="brasil" {{ old('country') === 'brasil' ? 'selected' : '' }}>Brasil</option>
                <option value="paraguai" {{ old('country') === 'paraguai' ? 'selected' : '' }}>Paraguai</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="sax-label" id="address-postal-label">CEP</label>
            <input name="postal_code" id="address-postal-code" class="form-control sax-input" value="{{ old('postal_code') }}" placeholder="00000-000">
        </div>
        <div class="col-md-4">
            <label class="sax-label" id="address-state-label">Estado</label>
            <select name="state" id="address-state" class="form-select sax-input" data-selected="{{ old('state') }}">
                <option value="">Selecione...</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="sax-label">Cidade</label>
            <select name="city" id="address-city" class="form-select sax-input" data-selected="{{ old('city') }}" disabled>
                <option value="">Selecione o estado primeiro...</option>
            </select>
        </div>
        <div class="col-md-8"><label class="sax-label">Rua / Endereço</label><input name="street" id="address-street" class="form-control sax-input" value="{{ old('street') }}" required></div>
        <div class="col-md-4"><label class="sax-label">Número</label><input name="number" class="form-control sax-input" value="{{ old('number') }}" required></div>
        <div class="col-md-6"><label class="sax-label">Bairro</label><input name="district" class="form-control sax-input" value="{{ old('district') }}" required></div>
        <div class="col-md-6"><label class="sax-label">Complemento</label><input name="complement" class="form-control sax-input" value="{{ old('complement') }}"></div>
        <div class="col-12">
            <label class="d-flex align-items-center gap-2"><input type="checkbox" name="is_default" value="1"><span>Definir como endereço padrão</span></label>
        </div>
        <div class="col-12 text-end"><button class="btn btn-dark px-5">Salvar endereço</button></div>
    </form>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/user-addresses.js') }}?v={{ filemtime(public_path('js/user-addresses.js')) }}"></script>
@endpush
