@extends('layout.dashboard')

@section('content')
<div class="sax-edit-wrapper">
    <div class="dashboard-header mb-5">
        <h2 class="sax-title text-uppercase letter-spacing-2">{{ __('messages.actualizar_registro') }}</h2>
        <div class="sax-divider-dark"></div>
    </div>

    {{-- BLOCO DE ALERTAS - ESSENCIAL PARA SABER POR QUE NÃO SALVOU --}}
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

    {{-- Verifique se a rota é PUT e se o @method('PUT') está presente --}}
    <form action="{{ route('user.profile.update') }}" method="POST" class="sax-premium-form">
        @csrf
        @method('PUT')

        <div class="row g-4">
            {{-- Nome --}}
            <div class="col-md-6 mb-3">
                <label class="sax-label">{{ __('messages.nome_completo') }}</label>
                <input type="text" name="name" class="form-control sax-input" value="{{ old('name', auth()->user()->name) }}" required>
            </div>

            {{-- Email --}}
            <div class="col-md-6 mb-3">
                <label class="sax-label">{{ __('messages.email') }}</label>
                <input type="email" name="email" class="form-control sax-input" value="{{ old('email', auth()->user()->email) }}" required>
            </div>

            {{-- Telefone --}}
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

            {{-- Bloco de Endereço Dinâmico --}}
            <div class="col-12">
                <label class="sax-label mb-3">{{ __('messages.localizacao_endereco') }}</label>
                <div class="row g-3 p-3 rounded" style="background: #f9f9f9;">
                    
                    {{-- País --}}
                    <div class="col-md-12">
                        <label class="small text-muted text-uppercase fw-bold">País</label>
                        <select name="country" id="country" class="form-select sax-input">
                            <option value="brasil" {{ old('country', auth()->user()->country) == 'brasil' ? 'selected' : '' }}>Brasil</option>
                            <option value="paraguai" {{ old('country', auth()->user()->country) == 'paraguai' ? 'selected' : '' }}>Paraguai</option>
                        </select>
                    </div>

                    {{-- CEP / Código Postal --}}
                    <div class="col-md-4">
                        <label class="small text-muted text-uppercase fw-bold" id="label-postal">CEP</label>
                        {{-- MUDANÇA: name="postal_code" para bater com o seu script --}}
                        <input type="text" name="postal_code" id="postal_code" class="form-control sax-input" value="{{ old('cep', auth()->user()->cep) }}" placeholder="00000-000">
                    </div>

                    {{-- Estado / Departamento --}}
                    <div class="col-md-4">
                        <label class="small text-muted text-uppercase fw-bold" id="label-state">Estado</label>
                        <select id="state-select" name="state" class="form-select sax-input" data-selected="{{ old('state', auth()->user()->state) }}">
                            <option value="">Selecione...</option>
                        </select>
                    </div>

                    {{-- Cidade --}}
                    <div class="col-md-4">
                        <label class="small text-muted text-uppercase fw-bold">Cidade</label>
                        <select id="city-select" name="city" class="form-select sax-input" data-selected="{{ old('city', auth()->user()->city) }}" disabled>
                            <option value="">Selecione o estado...</option>
                        </select>
                    </div>

                    {{-- Rua --}}
                    <div class="col-md-8">
                        <label class="small text-muted text-uppercase fw-bold">Rua / Endereço</label>
                        <input type="text" name="address" class="form-control sax-input" value="{{ old('address', auth()->user()->address) }}">
                    </div>

                    {{-- Número --}}
                    <div class="col-md-4">
                        <label class="small text-muted text-uppercase fw-bold">Número</label>
                        <input type="text" name="number" class="form-control sax-input" value="{{ old('number', auth()->user()->number) }}">
                    </div>

                    {{-- Bairro --}}
                    <div class="col-md-6">
                        <label class="small text-muted text-uppercase fw-bold">Bairro</label>
                        <input type="text" name="district" class="form-control sax-input" value="{{ old('district', auth()->user()->district) }}">
                    </div>

                    {{-- Complemento --}}
                    <div class="col-md-6">
                        <label class="small text-muted text-uppercase fw-bold">Complemento</label>
                        <input type="text" name="complement" class="form-control sax-input" value="{{ old('complement', auth()->user()->complement) }}">
                    </div>
                </div>
            </div>

            {{-- Documento --}}
            <div class="col-md-6 mb-3 mt-4">
                <label class="sax-label">{{ __('messages.num_documento') }}</label>
                <input type="text" name="document" class="form-control sax-input" value="{{ old('document', auth()->user()->document) }}">
            </div>

            {{-- Cadastro na Sax --}}
            <div class="col-md-6 mb-3 mt-4">
                <label class="sax-label">{{ __('messages.ja_possui_registro') }}</label>
                <select name="already_registered" id="already_registered" class="form-select sax-input">
                    <option value="1" {{ auth()->user()->already_registered ? 'selected' : '' }}>{{ __('messages.sim') }}</option>
                    <option value="0" {{ !auth()->user()->already_registered ? 'selected' : '' }}>{{ __('messages.nao') }}</option>
                </select>
            </div>

            {{-- Número do cadastro --}}
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

{{-- SCRIPT PARA O TOGGLE DO CAMPO SAX NUMBER --}}
<script>
    document.getElementById('already_registered').addEventListener('change', function() {
        const field = document.getElementById('sax_number_field');
        field.style.display = (this.value == '1') ? 'block' : 'none';
    });
</script>

{{-- JS migrado a app-custom.js --}}
@endsection
<style>
    /* Container e Header */
.sax-edit-wrapper {
    font-family: 'Inter', sans-serif;
    max-width: 800px;
}

.sax-title {
    font-weight: 700;
    font-size: 1.5rem;
    color: #1a1a1a;
}

.sax-divider-dark {
    width: 50px;
    height: 3px;
    background-color: #000;
    margin-top: 15px;
}

/* Formulário e Inputs */
.sax-label {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 1.2px;
    color: #888;
    margin-bottom: 10px;
}

.sax-input {
    border: none !important;
    border-bottom: 1px solid #e0e0e0 !important;
    border-radius: 0 !important;
    padding: 12px 0 !important;
    background-color: transparent !important;
    font-size: 0.95rem;
    color: #1a1a1a;
    transition: border-color 0.3s ease;
}

.sax-input:focus {
    box-shadow: none !important;
    border-color: #000 !important;
    color: #000;
}

/* Selects */
select.sax-input {
    background-position: right 0 center;
    padding-right: 20px !important;
}

/* Botão de Envio */
.btn-sax-submit {
    border-radius: 0;
    font-size: 0.8rem;
    background-color: #000;
    border: 1px solid #000;
    transition: all 0.4s ease;
}

.btn-sax-submit:hover {
    background-color: #fff;
    color: #000;
}

/* Responsividade */
@media (max-width: 768px) {
    .sax-edit-wrapper {
        padding: 0 15px;
    }
}
</style>
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
            // Se o país mudar, resetamos os selects
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
                    
                    // Dispara a carga das cidades se houver estado selecionado
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
                        // Limpa o data-selected após a primeira carga para não travar em mudanças manuais
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

        // Inicialização
        if (countryEl.value) {
            updateLabels();
        }

    } catch (e) {
        console.warn("Script de endereços isolado de erro externo:", e);
    }
});
</script>