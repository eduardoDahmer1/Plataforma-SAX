@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="DHL Express"
        description="Gerencie o sandbox MyDHL, a origem da SAX e o pacote usado nos testes de cotação." />

    <x-admin.alert />

    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('admin.dhl.measurements.index') }}" class="btn btn-outline-dark"><i class="fa-solid fa-ruler-combined me-2"></i>Medidas médias por categoria</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Revise os campos abaixo:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('dhl_test_result'))
        @php
            $testResult = session('dhl_test_result');
        @endphp
        <div class="alert {{ $testResult['success'] ? 'alert-success' : 'alert-danger' }} border-0">
            <div class="d-flex align-items-start gap-3">
                <i class="fa-solid {{ $testResult['success'] ? 'fa-circle-check' : 'fa-circle-xmark' }} fs-4 mt-1"></i>
                <div class="flex-grow-1">
                    <strong>{{ $testResult['message'] }}</strong>
                    @if (!empty($testResult['status']))
                        <div class="small mt-1">HTTP {{ $testResult['status'] }}</div>
                    @endif
                    @if (!empty($testResult['request_id']))
                        <div class="small text-break">Request ID: {{ $testResult['request_id'] }}</div>
                    @endif
                    @if (!empty($testResult['products']))
                        <div class="table-responsive mt-3">
                            <table class="table table-sm mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Serviço</th>
                                        <th>Valor DHL</th>
                                        @if (!empty($testResult['markup_enabled']))
                                            <th>Acréscimo ({{ number_format((float) $testResult['markup_percent'], 2, ',', '.') }}%)</th>
                                            <th>Valor total</th>
                                        @endif
                                        <th>Entrega estimada</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($testResult['products'] as $product)
                                        @php
                                            $providerPrice = $product['provider_price'] ?? $product['price'] ?? null;
                                        @endphp
                                        <tr>
                                            <td>{{ $product['code'] }}</td>
                                            <td>{{ $product['name'] }}</td>
                                            <td>{{ $providerPrice !== null ? $product['currency'].' '.number_format((float) $providerPrice, 2, ',', '.') : '—' }}</td>
                                            @if (!empty($testResult['markup_enabled']))
                                                <td>+ {{ isset($product['markup_value']) ? $product['currency'].' '.number_format((float) $product['markup_value'], 2, ',', '.') : '—' }}</td>
                                                <td><strong>{{ isset($product['total_price']) ? $product['currency'].' '.number_format((float) $product['total_price'], 2, ',', '.') : '—' }}</strong></td>
                                            @endif
                                            <td>{{ $product['delivery'] ?: '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="alert alert-warning border-0 mb-4">
        <strong><i class="fa-solid fa-shield-halved me-2"></i>Credenciais protegidas:</strong>
        chave, segredo e conta são criptografados no banco. Eles nunca são devolvidos ao navegador; deixe um campo de credencial vazio para preservar o valor atual.
    </div>

    <form method="POST" action="{{ route('admin.dhl.update') }}" autocomplete="off">
        @csrf
        @method('PUT')

        <section class="border rounded-3 p-3 p-lg-4 mb-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center mb-4">
                <div>
                    <h2 class="h6 text-uppercase fw-bold mb-1">Ambiente e ativação</h2>
                    <p class="small text-muted mb-0">No stage, mantenha o ambiente em sandbox.</p>
                </div>
                <div class="form-check form-switch fs-5">
                    <input type="hidden" name="enabled" value="0">
                    <input class="form-check-input" type="checkbox" role="switch" name="enabled" value="1" id="dhl_enabled"
                        @checked(old('enabled', $setting->enabled))>
                    <label class="form-check-label fs-6 fw-bold" for="dhl_enabled">Integração habilitada</label>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold" for="environment">Ambiente</label>
                    <select class="form-select" id="environment" name="environment" required>
                        <option value="sandbox" @selected(old('environment', $setting->environment) === 'sandbox')>Sandbox / testes</option>
                        <option value="production" @selected(old('environment', $setting->environment) === 'production')>Produção</option>
                    </select>
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-bold">Endpoint calculado</label>
                    <input class="form-control bg-light" readonly value="{{ old('environment', $setting->environment) === 'production' ? 'https://express.api.dhl.com/mydhlapi' : 'https://express.api.dhl.com/mydhlapi/test' }}">
                    <div class="form-text">A URL é fixada pela aplicação para impedir redirecionamento de credenciais.</div>
                </div>
            </div>
        </section>

        <details class="border rounded-3 p-3 p-lg-4 mb-4">
            <summary class="fw-bold text-uppercase" style="cursor:pointer">
                Configurações avançadas
                <span class="d-block small text-muted fw-normal mt-1">Credenciais, regras comerciais, embalagens, promoções e dados do remetente.</span>
            </summary>
            <div class="mt-4">
        <section class="border rounded-3 p-3 p-lg-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="h6 text-uppercase fw-bold mb-1">Credenciais MyDHL</h2>
                    <p class="small text-muted mb-0">Preencha somente para cadastrar ou substituir.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge {{ $credentials['api_key'] ? 'bg-success' : 'bg-secondary' }}">Chave {{ $credentials['api_key'] ? 'salva' : 'ausente' }}</span>
                    <span class="badge {{ $credentials['api_secret'] ? 'bg-success' : 'bg-secondary' }}">Segredo {{ $credentials['api_secret'] ? 'salvo' : 'ausente' }}</span>
                    <span class="badge {{ $credentials['account_number'] ? 'bg-success' : 'bg-secondary' }}">Conta {{ $credentials['account_number'] ? $credentials['account_masked'] : 'ausente' }}</span>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-lg-4">
                    <label class="form-label fw-bold" for="api_key">Chave da API</label>
                    <input type="password" class="form-control" id="api_key" name="api_key" value="" autocomplete="new-password" placeholder="Deixe vazio para preservar">
                </div>
                <div class="col-lg-4">
                    <label class="form-label fw-bold" for="api_secret">Segredo da API</label>
                    <input type="password" class="form-control" id="api_secret" name="api_secret" value="" autocomplete="new-password" placeholder="Deixe vazio para preservar">
                </div>
                <div class="col-lg-4">
                    <label class="form-label fw-bold" for="account_number">Número da conta DHL</label>
                    <input type="password" class="form-control" id="account_number" name="account_number" value="" autocomplete="new-password" placeholder="{{ $credentials['account_masked'] ?: 'Informe a conta' }}">
                </div>
            </div>
        </section>

        <section class="border rounded-3 p-3 p-lg-4 mb-4">
            <h2 class="h6 text-uppercase fw-bold mb-3">Regras comerciais</h2>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold" for="currency">Moeda</label>
                    <input class="form-control text-uppercase" id="currency" name="currency" maxlength="3" value="{{ old('currency', $setting->currency) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold" for="unit_of_measurement">Medidas</label>
                    <select class="form-select" id="unit_of_measurement" name="unit_of_measurement">
                        <option value="metric" selected>Métrico — kg/cm</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold" for="duties_taxes_payer">Impostos no destino</label>
                    <select class="form-select" id="duties_taxes_payer" name="duties_taxes_payer">
                        <option value="receiver" @selected(old('duties_taxes_payer', $setting->duties_taxes_payer) === 'receiver')>Cliente / destinatário</option>
                        <option value="shipper" @selected(old('duties_taxes_payer', $setting->duties_taxes_payer) === 'shipper')>SAX / remetente</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold" for="incoterm">Incoterm</label>
                    <select class="form-select" id="incoterm" name="incoterm">
                        <option value="DAP" @selected(old('incoterm', $setting->incoterm) === 'DAP')>DAP</option>
                        <option value="DDP" @selected(old('incoterm', $setting->incoterm) === 'DDP')>DDP</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold" for="excluded_country_codes">Países sem cotação DHL</label>
                    <input class="form-control text-uppercase" id="excluded_country_codes" name="excluded_country_codes"
                        value="{{ old('excluded_country_codes', implode(',', $setting->excluded_country_codes ?? ['PY'])) }}"
                        placeholder="PY">
                    <div class="form-text">Códigos ISO-2 separados por vírgula. O padrão atual exclui somente o Paraguai; o Brasil usa DHL.</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" for="volumetric_divisor">Divisor do peso volumétrico</label>
                    <input type="number" step="1" min="1000" max="10000" class="form-control" id="volumetric_divisor" name="volumetric_divisor"
                        value="{{ old('volumetric_divisor', $setting->volumetric_divisor) }}" required>
                    <div class="form-text">DHL Express: comprimento × largura × altura ÷ 5.000.</div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <label class="form-label fw-bold" for="rate_markup_percent">Acréscimo sobre a tarifa DHL (%)</label>
                        <div class="form-check form-switch mb-2">
                            <input type="hidden" name="rate_markup_enabled" value="0">
                            <input class="form-check-input" type="checkbox" role="switch" id="rate_markup_enabled" name="rate_markup_enabled" value="1"
                                @checked(old('rate_markup_enabled', $setting->rate_markup_enabled))>
                            <label class="form-check-label small" for="rate_markup_enabled">Ativo</label>
                        </div>
                    </div>
                    <input type="number" step="0.01" min="0" max="100" class="form-control" id="rate_markup_percent" name="rate_markup_percent"
                        value="{{ old('rate_markup_percent', $setting->rate_markup_percent) }}" data-rate-markup-input required>
                    <div class="form-text">Quando desativado, o checkout usa exatamente a tarifa retornada pela DHL.</div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check form-switch mb-2">
                        <input type="hidden" name="fallback_measurements_enabled" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" id="fallback_measurements_enabled" name="fallback_measurements_enabled" value="1"
                            @checked(old('fallback_measurements_enabled', $setting->fallback_measurements_enabled))>
                        <label class="form-check-label fw-bold" for="fallback_measurements_enabled">Usar perfis estimados quando o produto não tiver medidas</label>
                    </div>
                </div>
            </div>
        </section>

        <section class="border rounded-3 p-3 p-lg-4 mb-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
                <div>
                    <h2 class="h6 text-uppercase fw-bold mb-1">Embalagens e empacotamento automático</h2>
                    <p class="small text-muted mb-0">Linha completa padronizada: Envelope 1 e DHL Box 2 a Box 8. Confirme com a DHL Paraguai quais modelos estão disponíveis localmente.</p>
                </div>
                <div class="small text-muted">Peso faturável = maior entre peso real e volumétrico.</div>
            </div>

            <div class="row g-3">
                @foreach ($packages as $index => $package)
                    <div class="col-12">
                        <div class="border rounded-3 p-3 bg-light">
                            <input type="hidden" name="packages[{{ $index }}][id]" value="{{ $package->id }}">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div><strong>{{ $package->name }}</strong> <span class="badge bg-secondary ms-2">{{ $package->dhl_package_code }}</span></div>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="packages[{{ $index }}][active]" value="0">
                                    <input class="form-check-input" type="checkbox" name="packages[{{ $index }}][active]" value="1"
                                        id="package_active_{{ $package->id }}" @checked(old("packages.{$index}.active", $package->active))>
                                    <label class="form-check-label" for="package_active_{{ $package->id }}">Ativa</label>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-lg-4"><label class="form-label">Nome</label><input class="form-control" name="packages[{{ $index }}][name]" value="{{ old("packages.{$index}.name", $package->name) }}" required></div>
                                <div class="col-lg-2"><label class="form-label">Código DHL</label><input class="form-control text-uppercase" name="packages[{{ $index }}][dhl_package_code]" value="{{ old("packages.{$index}.dhl_package_code", $package->dhl_package_code) }}"></div>
                                <div class="col-4 col-lg-2"><label class="form-label">Comp. (cm)</label><input type="number" step="0.01" class="form-control" name="packages[{{ $index }}][length_cm]" value="{{ old("packages.{$index}.length_cm", $package->length_cm) }}" required></div>
                                <div class="col-4 col-lg-2"><label class="form-label">Larg. (cm)</label><input type="number" step="0.01" class="form-control" name="packages[{{ $index }}][width_cm]" value="{{ old("packages.{$index}.width_cm", $package->width_cm) }}" required></div>
                                <div class="col-4 col-lg-2"><label class="form-label">Alt. (cm)</label><input type="number" step="0.01" class="form-control" name="packages[{{ $index }}][height_cm]" value="{{ old("packages.{$index}.height_cm", $package->height_cm) }}" required></div>
                                <div class="col-6 col-lg-3"><label class="form-label">Peso da caixa (kg)</label><input type="number" step="0.001" class="form-control" name="packages[{{ $index }}][tare_weight_kg]" value="{{ old("packages.{$index}.tare_weight_kg", $package->tare_weight_kg) }}" required></div>
                                <div class="col-6 col-lg-3"><label class="form-label">Peso bruto máximo (kg)</label><input type="number" step="0.001" class="form-control" name="packages[{{ $index }}][max_gross_weight_kg]" value="{{ old("packages.{$index}.max_gross_weight_kg", $package->max_gross_weight_kg) }}" required></div>
                                <div class="col-6 col-lg-3"><label class="form-label">Máximo de itens</label><input type="number" step="1" class="form-control" name="packages[{{ $index }}][max_items]" value="{{ old("packages.{$index}.max_items", $package->max_items) }}" required></div>
                                <div class="col-6 col-lg-3"><label class="form-label">Ocupação segura (%)</label><input type="number" step="0.01" class="form-control" name="packages[{{ $index }}][fill_ratio_percent]" value="{{ old("packages.{$index}.fill_ratio_percent", $package->fill_ratio_percent) }}" required></div>
                                <div class="col-12"><label class="form-label">Exemplos de produtos</label><textarea class="form-control" rows="2" name="packages[{{ $index }}][product_examples]">{{ old("packages.{$index}.product_examples", $package->product_examples) }}</textarea></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="border rounded-3 p-3 p-lg-4 mb-4">
            <div class="d-flex justify-content-between gap-3 mb-3">
                <div>
                    <h2 class="h6 text-uppercase fw-bold mb-1">Promoção de frete grátis DHL</h2>
                    <p class="small text-muted mb-0">Todos os limites preenchidos precisam ser atendidos. Deixe desativado até a regra comercial ser aprovada.</p>
                </div>
                <div class="form-check form-switch">
                    <input type="hidden" name="free_shipping_enabled" value="0">
                    <input class="form-check-input" type="checkbox" name="free_shipping_enabled" value="1" id="free_shipping_enabled"
                        @checked(old('free_shipping_enabled', $setting->free_shipping_enabled))>
                    <label class="form-check-label fw-bold" for="free_shipping_enabled">Ativar</label>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label fw-bold">Mínimo de itens</label><input type="number" min="1" class="form-control" name="free_shipping_min_items" value="{{ old('free_shipping_min_items', $setting->free_shipping_min_items) }}" placeholder="Ex.: 3"></div>
                <div class="col-md-4"><label class="form-label fw-bold">Subtotal mínimo (USD)</label><input type="number" min="0" step="0.01" class="form-control" name="free_shipping_min_subtotal" value="{{ old('free_shipping_min_subtotal', $setting->free_shipping_min_subtotal) }}"></div>
                <div class="col-md-4"><label class="form-label fw-bold">Peso faturável máximo (kg)</label><input type="number" min="0.001" step="0.001" class="form-control" name="free_shipping_max_billable_weight_kg" value="{{ old('free_shipping_max_billable_weight_kg', $setting->free_shipping_max_billable_weight_kg) }}" placeholder="Ex.: 1"></div>
            </div>
        </section>

        <section class="border rounded-3 p-3 p-lg-4 mb-4">
            <h2 class="h6 text-uppercase fw-bold mb-1">Origem / remetente</h2>
            <p class="small text-muted mb-3">Endereço físico da SAX onde os volumes serão preparados.</p>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label fw-bold">Razão social</label><input class="form-control" name="origin_company_name" value="{{ old('origin_company_name', $setting->origin_company_name) }}" required></div>
                <div class="col-md-6"><label class="form-label fw-bold">Nome comercial</label><input class="form-control" name="origin_trading_name" value="{{ old('origin_trading_name', $setting->origin_trading_name) }}"></div>
                <div class="col-md-4"><label class="form-label fw-bold">RUC</label><input class="form-control" name="origin_tax_id" value="{{ old('origin_tax_id', $setting->origin_tax_id) }}" required></div>
                <div class="col-md-4"><label class="form-label fw-bold">País ISO-2</label><input class="form-control text-uppercase" maxlength="2" name="origin_country_code" value="{{ old('origin_country_code', $setting->origin_country_code) }}" required></div>
                <div class="col-md-4"><label class="form-label fw-bold">Código postal</label><input class="form-control" name="origin_postal_code" value="{{ old('origin_postal_code', $setting->origin_postal_code) }}" required></div>
                <div class="col-md-6"><label class="form-label fw-bold">Endereço — linha 1</label><input class="form-control" name="origin_address_line_1" value="{{ old('origin_address_line_1', $setting->origin_address_line_1) }}" required></div>
                <div class="col-md-6"><label class="form-label fw-bold">Endereço — linha 2</label><input class="form-control" name="origin_address_line_2" value="{{ old('origin_address_line_2', $setting->origin_address_line_2) }}"></div>
                <div class="col-md-4"><label class="form-label fw-bold">Cidade</label><input class="form-control" name="origin_city_name" value="{{ old('origin_city_name', $setting->origin_city_name) }}" required></div>
                <div class="col-md-4"><label class="form-label fw-bold">Departamento</label><input class="form-control" name="origin_province_name" value="{{ old('origin_province_name', $setting->origin_province_name) }}"></div>
                <div class="col-md-2"><label class="form-label fw-bold">Código depto.</label><input class="form-control" name="origin_province_code" value="{{ old('origin_province_code', $setting->origin_province_code) }}"></div>
                <div class="col-md-2"><label class="form-label fw-bold">Bairro</label><input class="form-control" name="origin_district_name" value="{{ old('origin_district_name', $setting->origin_district_name) }}"></div>
                <div class="col-md-4"><label class="form-label fw-bold">Responsável</label><input class="form-control" name="origin_contact_name" value="{{ old('origin_contact_name', $setting->origin_contact_name) }}" required></div>
                <div class="col-md-4"><label class="form-label fw-bold">Telefone operacional</label><input class="form-control" name="origin_phone" value="{{ old('origin_phone', $setting->origin_phone) }}" required></div>
                <div class="col-md-4"><label class="form-label fw-bold">E-mail operacional</label><input type="email" class="form-control" name="origin_email" value="{{ old('origin_email', $setting->origin_email) }}" required></div>
            </div>
        </section>
            </div>
        </details>

        <section class="border border-warning rounded-3 p-3 p-lg-4 mb-4 bg-warning-subtle">
            <h2 class="h6 text-uppercase fw-bold mb-1">Pacote de teste do sandbox</h2>
            <p class="small mb-3">Escolha a embalagem e informe somente o peso total, o valor e o destino. Código DHL e dimensões são preenchidos automaticamente.</p>
            @php
                $testPackageId = (int) old('test_package_id', $setting->test_package_id ?: optional($packages->firstWhere('code', 'small'))->id);
            @endphp
            <div class="row g-3">
                <div class="col-lg-6">
                    <label class="form-label fw-bold" for="test_package_id">Embalagem DHL</label>
                    <select class="form-select" id="test_package_id" name="test_package_id" required>
                        @foreach ($packages as $package)
                            <option value="{{ $package->id }}"
                                    data-code="{{ $package->dhl_package_code }}"
                                    data-length="{{ $package->length_cm }}"
                                    data-width="{{ $package->width_cm }}"
                                    data-height="{{ $package->height_cm }}"
                                    data-max-weight="{{ $package->max_gross_weight_kg }}"
                                    @selected($testPackageId === $package->id)>
                                {{ $package->name }} — {{ number_format($package->length_cm, 1, ',', '.') }} × {{ number_format($package->width_cm, 1, ',', '.') }} × {{ number_format($package->height_cm, 1, ',', '.') }} cm
                            </option>
                        @endforeach
                    </select>
                    <div id="dhl-test-package-hint" class="form-text"></div>
                </div>
                <div class="col-6 col-lg-3">
                    <label class="form-label fw-bold" for="test_weight_kg">Peso total (kg)</label>
                    <input type="number" step="0.001" min="0.001" class="form-control" id="test_weight_kg" name="test_weight_kg" value="{{ old('test_weight_kg', $setting->test_weight_kg) }}" required>
                    <div class="form-text">Produto mais a embalagem.</div>
                </div>
                <div class="col-6 col-lg-3"><label class="form-label fw-bold">Valor declarado (USD)</label><input type="number" step="0.01" min="0.01" class="form-control" name="test_declared_value" value="{{ old('test_declared_value', $setting->test_declared_value) }}" required></div>
                <input type="hidden" name="test_length_cm" value="{{ old('test_length_cm', $setting->test_length_cm) }}">
                <input type="hidden" name="test_width_cm" value="{{ old('test_width_cm', $setting->test_width_cm) }}">
                <input type="hidden" name="test_height_cm" value="{{ old('test_height_cm', $setting->test_height_cm) }}">
            </div>

            @php
                $testCountryCode = strtoupper((string) old('test_destination_country_code', $setting->test_destination_country_code));
                $testProvinceCode = (string) old('test_destination_province_code', $setting->test_destination_province_code);
                $testProvinceName = (string) old('test_destination_province_name', $setting->test_destination_province_name);
                $excludedTestCountries = collect($setting->excluded_country_codes ?? ['PY'])
                    ->map(fn ($code) => strtoupper((string) $code))
                    ->all();
            @endphp
            <div id="dhl-test-destination"
                 class="row g-3 mt-0"
                 data-countries-url="{{ route('admin.dhl.locations.countries') }}"
                 data-subdivisions-url="{{ route('admin.dhl.locations.subdivisions') }}"
                 data-cities-url="{{ route('admin.dhl.locations.cities') }}"
                 data-postal-codes-url="{{ route('admin.dhl.locations.postal-codes') }}">
                <div class="col-md-6 col-xl-3">
                    <label class="form-label fw-bold" for="test_destination_country_code">País de destino</label>
                    <select class="form-select" id="test_destination_country_code" name="test_destination_country_code" required>
                        <option value="">Selecione o país</option>
                        @foreach ($destinationCountries as $country)
                            @php($isExcluded = in_array($country['iso2'], $excludedTestCountries, true))
                            <option value="{{ $country['iso2'] }}"
                                    @selected($testCountryCode === $country['iso2'])
                                    @disabled($isExcluded)>
                                {{ $country['name'] }} ({{ $country['iso2'] }}){{ $isExcluded ? ' — sem DHL' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 col-xl-3">
                    <label class="form-label fw-bold" for="test_destination_province_code">Estado / província / região</label>
                    <select class="form-select"
                            id="test_destination_province_code"
                            name="test_destination_province_code"
                            data-selected-code="{{ $testProvinceCode }}"
                            data-selected-name="{{ $testProvinceName }}">
                        <option value="">Selecione primeiro o país</option>
                    </select>
                    <input type="hidden" id="test_destination_province_name" name="test_destination_province_name" value="{{ $testProvinceName }}">
                </div>
                <div class="col-md-6 col-xl-3">
                    <label class="form-label fw-bold" for="test_destination_city">Cidade de teste</label>
                    <input class="form-control"
                           id="test_destination_city"
                           name="test_destination_city"
                           list="dhl-test-city-options"
                           value="{{ old('test_destination_city', $setting->test_destination_city) }}"
                           autocomplete="off"
                           required>
                    <datalist id="dhl-test-city-options"></datalist>
                    <div class="form-text">Selecione uma sugestão ou digite a cidade.</div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <label class="form-label fw-bold" for="test_destination_postal_code">Código postal de teste</label>
                    <input class="form-control" id="test_destination_postal_code" name="test_destination_postal_code" list="dhl-test-postal-options" value="{{ old('test_destination_postal_code', $setting->test_destination_postal_code) }}" required autocomplete="off">
                    <datalist id="dhl-test-postal-options"></datalist>
                    <div id="dhl-test-postal-hint" class="form-text">Escolha uma sugestão ou informe o código postal exato do endereço.</div>
                </div>
                <div class="col-12">
                    <div id="dhl-location-status" class="form-text" role="status" aria-live="polite"></div>
                </div>
            </div>
        </section>

        <div class="d-flex flex-wrap gap-2 justify-content-end">
            <button type="submit" class="btn btn-dark px-4">
                <i class="fa-solid fa-floppy-disk me-2"></i>Salvar configurações
            </button>
        </div>
    </form>

    <form method="POST" action="{{ route('admin.dhl.test') }}" class="mt-3 text-end" data-dhl-test-form>
        @csrf
        @foreach ([
            'test_package_id',
            'test_weight_kg',
            'test_declared_value',
            'rate_markup_enabled',
            'rate_markup_percent',
            'test_destination_country_code',
            'test_destination_province_code',
            'test_destination_province_name',
            'test_destination_city',
            'test_destination_postal_code',
        ] as $testField)
            <input type="hidden" name="{{ $testField }}" value="{{ old($testField, $setting->{$testField}) }}" data-dhl-test-value="{{ $testField }}">
        @endforeach
        <button type="submit" class="btn btn-outline-warning px-4" data-dhl-test-submit>
            <i class="fa-solid fa-vial-circle-check me-2"></i>Testar sandbox com dados da tela
        </button>
        <div class="form-text">O teste usa imediatamente os valores preenchidos acima. Salve somente se quiser mantê-los para a próxima visita.</div>
    </form>
</x-admin.card>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin-dhl.js') }}?v={{ filemtime(public_path('js/admin-dhl.js')) }}"></script>
@endpush
