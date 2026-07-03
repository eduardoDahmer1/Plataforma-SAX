@extends('layout.dashboard')

@section('content')
<div class="sax-edit-wrapper">
    <div class="dashboard-header mb-5">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3">
            <div>
                <h2 class="sax-title text-uppercase letter-spacing-2 mb-2">{{ __('messages.actualizar_registro') }}</h2>
                <p class="text-muted mb-0">Mantenha seus dados atualizados para agilizar pedidos e entregas.</p>
            </div>
            <a href="{{ route('user.dashboard') }}" class="btn-back-minimal">
                <i class="fas fa-chevron-left me-1"></i> {{ __('messages.voltar') }}
            </a>
        </div>
        <div class="sax-divider-dark"></div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger shadow-sm mb-4">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success shadow-sm mb-4">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('user.profile.update') }}" method="POST" class="sax-premium-form card border-0 shadow-sm rounded-4 p-4 p-md-5">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-md-6 mb-3">
                <label class="sax-label">{{ __('messages.nome_completo') }}</label>
                <input type="text" name="name" class="form-control sax-input" value="{{ old('name', auth()->user()->name) }}" required>
            </div>

            <div class="col-md-6 mb-3">
                <label class="sax-label">{{ __('messages.email') }}</label>
                <input type="email" name="email" class="form-control sax-input" value="{{ old('email', auth()->user()->email) }}" required>
            </div>

            <div class="col-md-12 mb-3">
                <label class="sax-label">{{ __('messages.telefone') }}</label>
                <div class="d-flex gap-2">
                    <select name="phone_country" class="form-select sax-input w-auto">
                        <option value="55" {{ auth()->user()->phone_country == '55' ? 'selected' : '' }}>BRA (+55)</option>
                        <option value="595" {{ auth()->user()->phone_country == '595' ? 'selected' : '' }}>PRY (+595)</option>
                    </select>
                    <input type="text" name="phone_number" class="form-control sax-input flex-grow-1" value="{{ old('phone_number', auth()->user()->phone_number) }}">
                </div>
            </div>

            <div class="col-12">
                <label class="sax-label mb-3">{{ __('messages.localizacao_endereco') }}</label>
                <div class="row g-3 p-3 rounded-3 border" style="background: #fbfbfb;">
                    <div class="col-md-12">
                        <label class="small text-muted text-uppercase fw-bold">País</label>
                        <select name="country" id="country" class="form-select sax-input">
                            <option value="brasil" {{ old('country', auth()->user()->country) == 'brasil' ? 'selected' : '' }}>Brasil</option>
                            <option value="paraguai" {{ old('country', auth()->user()->country) == 'paraguai' ? 'selected' : '' }}>Paraguai</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="small text-muted text-uppercase fw-bold" id="label-postal">CEP</label>
                        <input type="text" name="postal_code" id="postal_code" class="form-control sax-input" value="{{ old('cep', auth()->user()->cep) }}" placeholder="00000-000">
                    </div>

                    <div class="col-md-4">
                        <label class="small text-muted text-uppercase fw-bold" id="label-state">Estado</label>
                        <select id="state-select" name="state" class="form-select sax-input" data-selected="{{ old('state', auth()->user()->state) }}">
                            <option value="">Selecione...</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="small text-muted text-uppercase fw-bold">Cidade</label>
                        <select id="city-select" name="city" class="form-select sax-input" data-selected="{{ old('city', auth()->user()->city) }}" disabled>
                            <option value="">Selecione o estado...</option>
                        </select>
                    </div>

                    <div class="col-md-8">
                        <label class="small text-muted text-uppercase fw-bold">Rua / Endereço</label>
                        <input type="text" name="address" class="form-control sax-input" value="{{ old('address', auth()->user()->address) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="small text-muted text-uppercase fw-bold">Número</label>
                        <input type="text" name="number" class="form-control sax-input" value="{{ old('number', auth()->user()->number) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="small text-muted text-uppercase fw-bold">Bairro</label>
                        <input type="text" name="district" class="form-control sax-input" value="{{ old('district', auth()->user()->district) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="small text-muted text-uppercase fw-bold">Complemento</label>
                        <input type="text" name="complement" class="form-control sax-input" value="{{ old('complement', auth()->user()->complement) }}">
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-3 mt-4">
                <label class="sax-label">{{ __('messages.num_documento') }}</label>
                <input type="text" name="document" class="form-control sax-input" value="{{ old('document', auth()->user()->document) }}">
            </div>

            <div class="col-md-6 mb-3 mt-4">
                <label class="sax-label">{{ __('messages.ja_possui_registro') }}</label>
                <select name="already_registered" id="already_registered" class="form-select sax-input">
                    <option value="1" {{ auth()->user()->already_registered ? 'selected' : '' }}>{{ __('messages.sim') }}</option>
                    <option value="0" {{ !auth()->user()->already_registered ? 'selected' : '' }}>{{ __('messages.nao') }}</option>
                </select>
            </div>

            <div class="col-md-12 mb-3" id="sax_number_field" style="display: {{ auth()->user()->already_registered ? 'block' : 'none' }};">
                <label class="sax-label">{{ __('messages.num_registro_interno') }}</label>
                <input type="text" name="additional_info" class="form-control sax-input" value="{{ old('additional_info', auth()->user()->additional_info ?? '') }}">
            </div>
        </div>

        <div class="d-flex justify-content-end mt-5">
            <button type="submit" class="btn btn-dark btn-sax-submit px-5 py-3 text-uppercase fw-bold letter-spacing-1">
                {{ __('messages.atualizar_dados_btn') }}
            </button>
        </div>
    </form>
</div>

<script>
    document.getElementById('already_registered').addEventListener('change', function() {
        const field = document.getElementById('sax_number_field');
        field.style.display = (this.value == '1') ? 'block' : 'none';
    });
</script>

@endsection

<script>
document.addEventListener('DOMContentLoaded', function () {
    try {
        const countryEl = document.getElementById('country');
        const stateEl = document.getElementById('state-select');
        const cityEl = document.getElementById('city-select');
        const postalEl = document.getElementById('postal_code');
        const labelPostal = document.getElementById('label-postal');

        if (!countryEl || !stateEl || !cityEl) return;

        function updateLabels() {
            stateEl.innerHTML = '<option value="">Carregando...</option>';
            cityEl.innerHTML = '<option value="">Selecione o estado...</option>';
            cityEl.disabled = true;

            if (countryEl.value === 'brasil') {
                if (labelPostal) labelPostal.innerText = "CEP";
                if (postalEl) postalEl.placeholder = "00000-000";
                loadStatesBR();
            } else if (countryEl.value === 'paraguai') {
                if (labelPostal) labelPostal.innerText = "Código Postal";
                if (postalEl) postalEl.placeholder = "Ex: 7000";
                loadStatesPY();
            }
        }

        function loadStatesBR() {
            const current = stateEl.getAttribute('data-selected');
            fetch('https://servicodados.ibge.gov.br/api/v1/localidades/estados?orderBy=nome')
                .then(r => r.json())
                .then(data => {
                    let options = '<option value="">Selecione...</option>';
                    data.forEach(uf => {
                        const sel = (uf.sigla === current) ? 'selected' : '';
                        options += `<option value="${uf.sigla}" data-id="${uf.id}" ${sel}>${uf.nome}</option>`;
                    });
                    stateEl.innerHTML = options;
                    if (stateEl.value) {
                        stateEl.dispatchEvent(new Event('change'));
                    }
                }).catch(err => console.error("Erro IBGE:", err));
        }

        function loadStatesPY() {
            const current = stateEl.getAttribute('data-selected');
            fetch('/data/py.json')
                .then(r => r.json())
                .then(data => {
                    const depts = [...new Set(data.map(i => i.admin_name))].sort();
                    let options = '<option value="">Selecione...</option>';
                    depts.forEach(d => {
                        const sel = (d === current) ? 'selected' : '';
                        options += `<option value="${d}" ${sel}>${d}</option>`;
                    });
                    stateEl.innerHTML = options;

                    if (stateEl.value) {
                        stateEl.dispatchEvent(new Event('change'));
                    }
                }).catch(err => {
                    console.error("Erro PY JSON:", err);
                    stateEl.innerHTML = '<option value="">Erro ao carregar</option>';
                });
        }

        stateEl.addEventListener('change', function () {
            const country = countryEl.value;
            const currentCity = cityEl.getAttribute('data-selected');

            if (!this.value || this.value === "Carregando...") {
                cityEl.disabled = true;
                return;
            }

            cityEl.disabled = false;
            cityEl.innerHTML = '<option value="">Carregando...</option>';

            if (country === 'brasil') {
                const id = this.options[this.selectedIndex].getAttribute('data-id');
                if (!id) return;
                fetch(`https://servicodados.ibge.gov.br/api/v1/localidades/estados/${id}/municipios`)
                    .then(r => r.json())
                    .then(data => {
                        let options = '<option value="">Selecione...</option>';
                        data.forEach(c => {
                            const sel = (c.nome === currentCity) ? 'selected' : '';
                            options += `<option value="${c.nome}" ${sel}>${c.nome}</option>`;
                        });
                        cityEl.innerHTML = options;
                        cityEl.removeAttribute('data-selected');
                    });
            } else if (country === 'paraguai') {
                const selectedDept = this.value;
                fetch('/data/py.json')
                    .then(r => r.json())
                    .then(data => {
                        const cities = data.filter(item => item.admin_name === selectedDept)
                            .map(item => item.city)
                            .sort();

                        let options = '<option value="">Selecione...</option>';
                        cities.forEach(cityName => {
                            const sel = (cityName === currentCity) ? 'selected' : '';
                            options += `<option value="${cityName}" ${sel}>${cityName}</option>`;
                        });
                        cityEl.innerHTML = options;
                        cityEl.removeAttribute('data-selected');
                    }).catch(err => {
                        cityEl.innerHTML = '<option value="">Erro ao carregar</option>';
                    });
            }
        });

        countryEl.addEventListener('change', updateLabels);

        if (countryEl.value) {
            updateLabels();
        }

    } catch (e) {
        console.warn("Script de endereços isolado de erro externo:", e);
    }
});
</script>