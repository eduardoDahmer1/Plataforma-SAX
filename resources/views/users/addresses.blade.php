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
            <div class="card h-100 border rounded-4 p-4 sax-address-card">
                <div class="d-flex justify-content-between gap-3">
                    <div>
                        <h5 class="fw-bold mb-2">{{ $address->label }}</h5>
                        <p class="text-muted mb-0">{{ $address->formatted() }}</p>
                    </div>
                    @if ($address->is_default)
                        <span class="badge bg-dark align-self-start">Padrão</span>
                    @endif
                </div>
                <div class="d-flex flex-wrap gap-2 mt-4 sax-address-actions">
                    <button type="button" class="btn btn-outline-dark btn-sm"
                            data-address-edit
                            data-action="{{ route('user.addresses.update', $address) }}"
                            data-address="{{ json_encode($address->only([
                                'id', 'label', 'country', 'postal_code', 'state', 'city', 'street',
                                'number', 'district', 'complement', 'is_default',
                            ])) }}">
                        <i class="fa-regular fa-pen-to-square me-1"></i> Editar
                    </button>
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

<div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 sax-address-form-card">
    <h4 class="fw-bold mb-4">Cadastrar novo endereço</h4>
    <form method="POST" action="{{ route('user.addresses.store') }}" class="row g-3" data-address-form>
        @csrf
        <div class="col-md-6">
            <label class="sax-label">Identificação</label>
            <input name="label" class="form-control sax-input" value="{{ old('editing_address_id') ? '' : old('label') }}" placeholder="Casa, Trabalho..." required>
        </div>
        <div class="col-md-6">
            <label class="sax-label">País</label>
            <select name="country" class="form-select sax-input" required data-address-country>
                <x-country-options :selected="!old('editing_address_id') ? old('country', 'brasil') : 'brasil'" />
            </select>
        </div>
        <div class="col-md-4">
            <label class="sax-label" data-address-postal-label>CEP</label>
            <input name="postal_code" class="form-control sax-input" value="{{ old('editing_address_id') ? '' : old('postal_code') }}" placeholder="00000-000" data-address-postal-code>
        </div>
        <div class="col-md-4">
            <label class="sax-label" data-address-state-label>Estado</label>
            <select name="state" class="form-select sax-input" data-selected="{{ old('editing_address_id') ? '' : old('state') }}" data-address-state required>
                <option value="">Selecione...</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="sax-label">Cidade</label>
            <select name="city" class="form-select sax-input" data-selected="{{ old('editing_address_id') ? '' : old('city') }}" disabled data-address-city required>
                <option value="">Selecione o estado primeiro...</option>
            </select>
        </div>
        <div class="col-md-8"><label class="sax-label">Rua / Endereço</label><input name="street" class="form-control sax-input" value="{{ old('editing_address_id') ? '' : old('street') }}" required data-address-street></div>
        <div class="col-md-4"><label class="sax-label">Número</label><input name="number" class="form-control sax-input" value="{{ old('editing_address_id') ? '' : old('number') }}" required></div>
        <div class="col-md-6"><label class="sax-label">Bairro</label><input name="district" class="form-control sax-input" value="{{ old('editing_address_id') ? '' : old('district') }}" required data-address-district></div>
        <div class="col-md-6"><label class="sax-label">Complemento</label><input name="complement" class="form-control sax-input" value="{{ old('editing_address_id') ? '' : old('complement') }}"></div>
        <div class="col-12">
            <label class="d-flex align-items-center gap-2"><input type="checkbox" name="is_default" value="1" {{ !old('editing_address_id') && old('is_default') ? 'checked' : '' }}><span>Definir como endereço padrão</span></label>
        </div>
        <div class="col-12 text-end"><button class="btn btn-dark px-5">Salvar endereço</button></div>
    </form>
</div>

<div class="modal fade" id="editAddressModal" tabindex="-1" aria-labelledby="editAddressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow">
            <form method="POST" action="#" data-address-form data-address-edit-form>
                @csrf
                @method('PATCH')
                <input type="hidden" name="editing_address_id" value="">

                <div class="modal-header border-0 px-4 px-md-5 pt-4 pb-2">
                    <div>
                        <h4 class="modal-title fw-bold" id="editAddressModalLabel">Editar endereço</h4>
                        <p class="text-muted small mb-0">Atualize os dados deste local de entrega.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <div class="modal-body px-4 px-md-5 pb-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="sax-label">Identificação</label>
                            <input name="label" class="form-control sax-input" required>
                        </div>
                        <div class="col-md-6">
                            <label class="sax-label">País</label>
                            <select name="country" class="form-select sax-input" required data-address-country>
                                <x-country-options selected="brasil" />
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="sax-label" data-address-postal-label>CEP</label>
                            <input name="postal_code" class="form-control sax-input" placeholder="00000-000" data-address-postal-code>
                        </div>
                        <div class="col-md-4">
                            <label class="sax-label" data-address-state-label>Estado</label>
                            <select name="state" class="form-select sax-input" data-address-state required>
                                <option value="">Selecione...</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="sax-label">Cidade</label>
                            <select name="city" class="form-select sax-input" disabled data-address-city required>
                                <option value="">Selecione o estado primeiro...</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="sax-label">Rua / Endereço</label>
                            <input name="street" class="form-control sax-input" required data-address-street>
                        </div>
                        <div class="col-md-4">
                            <label class="sax-label">Número</label>
                            <input name="number" class="form-control sax-input" required>
                        </div>
                        <div class="col-md-6">
                            <label class="sax-label">Bairro</label>
                            <input name="district" class="form-control sax-input" required data-address-district>
                        </div>
                        <div class="col-md-6">
                            <label class="sax-label">Complemento</label>
                            <input name="complement" class="form-control sax-input">
                        </div>
                        <div class="col-12">
                            <label class="d-flex align-items-center gap-2 mb-0">
                                <input type="checkbox" name="is_default" value="1" data-address-default>
                                <span>Definir como endereço padrão</span>
                            </label>
                            <small class="text-muted d-none mt-1" data-default-help>Este já é o endereço padrão da sua conta.</small>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 px-4 px-md-5 pb-4 pt-0">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-dark px-4">Salvar alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if (old('editing_address_id'))
    <div id="failed-address-edit"
         data-address-id="{{ old('editing_address_id') }}"
         data-address="{{ json_encode([
             'id' => old('editing_address_id'),
             'label' => old('label'),
             'country' => old('country'),
             'postal_code' => old('postal_code'),
             'state' => old('state'),
             'city' => old('city'),
             'street' => old('street'),
             'number' => old('number'),
             'district' => old('district'),
             'complement' => old('complement'),
             'is_default' => old('is_default', false),
         ]) }}"
         hidden></div>
@endif
@endsection

@push('scripts')
    <script src="{{ asset('js/user-addresses.js') }}?v={{ filemtime(public_path('js/user-addresses.js')) }}"></script>
@endpush
