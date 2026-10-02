@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="Controle operacional da loja"
        description="Ative ou pause recursos da loja imediatamente, sem alterar código ou credenciais." />
    <x-admin.alert />

    @php
        $temporaryTestActive = (bool) ($temporaryTest['active'] ?? false);
        $temporaryTestExpiresAt = $temporaryTest['expires_at'] ?? null;
        $catalogActuallyAvailable = (bool) ($catalogIntegrationStatus['actual_available'] ?? $catalogIntegrationStatus['available'] ?? true);
    @endphp

    <section class="temporary-test-card {{ $temporaryTestActive ? 'is-active' : '' }} mb-4"
             @if($temporaryTestActive && $temporaryTestExpiresAt) data-temporary-test-expires="{{ $temporaryTestExpiresAt->toIso8601String() }}" @endif>
        <div class="temporary-test-card__icon"><i class="fa-solid fa-stopwatch" aria-hidden="true"></i></div>
        <div class="temporary-test-card__content">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                <h2 class="temporary-test-card__title mb-0">Teste temporário do fluxo de compra</h2>
                <span class="temporary-test-card__status">{{ $temporaryTestActive ? 'ATIVO AGORA' : 'DESATIVADO' }}</span>
            </div>
            @if($temporaryTestActive)
                <p class="mb-1">Carrinho, checkout, DHL/localizações e formas de pagamento estão liberados temporariamente, mesmo com a integração indisponível.</p>
                <small>Expira automaticamente às <strong>{{ $temporaryTestExpiresAt?->format('H:i:s') }}</strong> · tempo restante: <strong data-temporary-test-countdown>calculando…</strong></small>
            @else
                <p class="mb-1">Libera todo o fluxo de compra por exatamente cinco minutos para testes controlados.</p>
                <small>A integração está <strong>{{ $catalogActuallyAvailable ? 'disponível' : 'indisponível' }}</strong>. Durante o teste, clientes comuns verão um aviso de atualização.</small>
            @endif
        </div>
        <div class="temporary-test-card__action">
            @if($temporaryTestActive)
                <form method="POST" action="{{ route('admin.store-controls.temporary-test.deactivate') }}" class="m-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-dark">Encerrar agora</button>
                </form>
            @else
                <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#temporaryPurchaseTestModal">
                    <i class="fa-solid fa-play me-2"></i>Ativar por 5 minutos
                </button>
            @endif
        </div>
    </section>

    <div class="modal fade" id="temporaryPurchaseTestModal" tabindex="-1" aria-labelledby="temporaryPurchaseTestModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content temporary-test-modal">
                <div class="modal-header">
                    <div>
                        <span class="temporary-test-modal__eyebrow">Confirmação obrigatória</span>
                        <h2 class="modal-title h5 mb-0" id="temporaryPurchaseTestModalLabel">Ativar o fluxo de compra por 5 minutos?</h2>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <p>Durante esse período, usuários comuns poderão usar normalmente:</p>
                    <ul class="temporary-test-modal__list">
                        <li>Carrinho e inclusão de produtos</li>
                        <li>Checkout e cálculo de frete/DHL</li>
                        <li>Depósito, Bancard, PIX e WhatsApp</li>
                        <li>Países e localizações usados na entrega</li>
                    </ul>
                    <div class="temporary-test-modal__notice">A liberação termina automaticamente após cinco minutos. Um aviso de atualização ficará visível para o cliente durante toda a janela.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Não, cancelar</button>
                    <form method="POST" action="{{ route('admin.store-controls.temporary-test.activate') }}" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-dark">Sim, ativar agora</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.store-controls.update') }}" class="mt-4">
        @csrf
        @method('PUT')

        <div class="alert alert-warning border-0 mb-4">
            <strong><i class="fa-solid fa-triangle-exclamation me-2"></i>Atenção:</strong>
            estas chaves entram em vigor assim que você guardar. Pedidos e pagamentos já criados continuam acessíveis para não interromper uma cobrança pendente.
        </div>

        <section class="store-profile-card mb-5">
            <div>
                <h2 class="h6 text-uppercase fw-bold mb-1">Identidade da loja</h2>
                <p class="text-muted small mb-0">Define qual experiência este endereço deve exibir. A opção Ótica prioriza categorias de ótica, óculos e lentes.</p>
            </div>
            <select class="form-select store-profile-select" name="store_profile" aria-label="Perfil da loja">
                <option value="stage" @selected(old('store_profile', $controls['store_profile'] ?? 'stage') === 'stage')>Stage / homologação</option>
                <option value="sax" @selected(old('store_profile', $controls['store_profile'] ?? 'stage') === 'sax')>SAX / plataforma completa</option>
                <option value="otica" @selected(old('store_profile', $controls['store_profile'] ?? 'stage') === 'otica')>Ótica / catálogo óptico</option>
            </select>
        </section>

        @php
            $groups = [
                'Fluxo de compra' => [
                    ['cart_enabled', 'Carrinho', 'Permite abrir e administrar a sacola. Desativado, também bloqueia novas inclusões e o acesso ao checkout.', 'fa-bag-shopping', 'danger'],
                    ['checkout_enabled', 'Checkout', 'Permite iniciar e concluir novos pedidos. Os itens já existentes permanecem guardados no carrinho.', 'fa-lock', 'warning'],
                    ['add_to_cart_enabled', 'Botão Adicionar ao carrinho', 'Permite incluir produtos. Quando desligado, o produto exibe apenas a consulta pelo WhatsApp.', 'fa-cart-plus', 'primary'],
                    ['whatsapp_enabled', 'Compra/consulta por WhatsApp', 'Mantém disponíveis as ações comerciais direcionadas ao WhatsApp.', 'fa-whatsapp', 'success', true],
                ],
                'Formas de pagamento' => [
                    ['deposit_enabled', 'Depósito / transferência', 'Exibe e aceita depósito ou transferência em novos pedidos.', 'fa-building-columns', 'secondary'],
                    ['bancard_enabled', 'Bancard V2', 'Exibe e aceita cartão e QR Bancard em novos pedidos.', 'fa-credit-card', 'primary'],
                    ['pix_enabled', 'Rendix Pix', 'Exibe e aceita Pix brasileiro pela Rendix em novos pedidos.', 'fa-pix', 'success', true],
                ],
                'Localizações internacionais' => [
                    ['geonames_enabled', 'GeoNames / países internacionais', 'Libera países, estados e cidades mundiais. Desligado, Brasil e Paraguai continuam funcionando normalmente.', 'fa-earth-americas', 'info'],
                ],
            ];
        @endphp

        @foreach($groups as $title => $items)
            <section class="mb-5">
                <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
                    <h2 class="h6 text-uppercase fw-bold mb-0">{{ $title }}</h2>
                    <small class="text-muted">Alteração manual</small>
                </div>
                <div class="row g-3">
                    @foreach($items as $item)
                        @php
                            [$field, $label, $description, $icon, $color] = $item;
                            $brandIcon = (bool) ($item[5] ?? false);
                        @endphp
                        <div class="col-12 col-xl-6">
                            <div class="store-control-card h-100 d-flex gap-3 align-items-start">
                                <span class="store-control-icon text-{{ $color }} bg-{{ $color }}-subtle"><i class="{{ $brandIcon ? 'fa-brands' : 'fa-solid' }} {{ $icon }}"></i></span>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center gap-3">
                                        <label class="fw-bold mb-0" for="{{ $field }}">{{ $label }}</label>
                                        <div class="form-check form-switch m-0">
                                            <input type="hidden" name="{{ $field }}" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                name="{{ $field }}" value="1" id="{{ $field }}"
                                                @checked(old($field, $controls[$field] ?? false))>
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-0 mt-2">{{ $description }}</p>
                                    <span class="badge mt-3 {{ ($controls[$field] ?? false) ? 'text-bg-success' : 'text-bg-danger' }}">
                                        {{ ($controls[$field] ?? false) ? 'Ativo agora' : 'Desativado agora' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach

        @php
            $navigationGroups = [
                'Visibilidade do cabeçalho' => ['header_categories_enabled' => 'Categorias', 'header_institucional_enabled' => 'Institucional', 'header_bridal_enabled' => 'Bridal', 'header_palace_enabled' => 'SAX Palace', 'header_cafe_enabled' => 'Café & Bistrô', 'header_blog_enabled' => '#SAXNEWS / Blog', 'header_contact_enabled' => 'Contato', 'header_guide_enabled' => 'Guia de atendimento'],
                'Visibilidade do rodapé' => ['footer_categories_enabled' => 'Categorias', 'footer_institucional_enabled' => 'Institucional', 'footer_bridal_enabled' => 'Bridal', 'footer_palace_enabled' => 'SAX Palace', 'footer_cafe_enabled' => 'Café & Bistrô', 'footer_blog_enabled' => '#SAXNEWS / Blog', 'footer_contact_enabled' => 'Contato', 'footer_guide_enabled' => 'Guia de atendimento'],
            ];
        @endphp
        @foreach($navigationGroups as $title => $items)
            <section class="mb-5">
                <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
                    <h2 class="h6 text-uppercase fw-bold mb-0">{{ $title }}</h2>
                    <small class="text-muted">Alteração manual</small>
                </div>
                <div class="row g-3">
                    @foreach($items as $field => $label)
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="store-nav-toggle d-flex align-items-center justify-content-between gap-3">
                                <span class="fw-semibold">{{ $label }}</span>
                                <div class="form-check form-switch m-0">
                                    <input type="hidden" name="{{ $field }}" value="0">
                                    <input class="form-check-input" type="checkbox" role="switch" name="{{ $field }}" value="1" id="{{ $field }}" @checked(old($field, $controls[$field] ?? true))>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach

        <div class="border-top pt-4 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
            <small class="text-muted"><i class="fa-solid fa-shield-halved me-1"></i>Somente Admin Master pode alterar estes controles.</small>
            <button class="btn btn-dark px-5 py-2 text-uppercase fw-bold" type="submit">
                <i class="fa-solid fa-floppy-disk me-2"></i>Guardar controles
            </button>
        </div>
    </form>
</x-admin.card>

<style>
    .temporary-test-card { display:grid; grid-template-columns:auto minmax(0,1fr) auto; align-items:center; gap:16px; padding:18px; background:#f5f5f4; border:1px solid #cfd1d4; border-left:4px solid #55595e; border-radius:5px; }
    .temporary-test-card.is-active { background:#ececeb; border-left-color:#25282c; }
    .temporary-test-card__icon { width:42px; height:42px; display:grid; place-items:center; background:#34373b; border-radius:4px; color:#fff; }
    .temporary-test-card__title { color:#202226; font-size:.82rem; font-weight:800; }
    .temporary-test-card__content p { color:#464a4f; font-size:.72rem; }
    .temporary-test-card__content small { color:#6d7176; font-size:.63rem; }
    .temporary-test-card__status { padding:4px 7px; background:#fff; border:1px solid #c7c9cc; border-radius:2px; color:#4b4f54; font-size:.52rem; font-weight:800; letter-spacing:.06em; }
    .temporary-test-card.is-active .temporary-test-card__status { background:#34373b; border-color:#34373b; color:#fff; }
    .temporary-test-card__action .btn { min-height:40px; border-radius:3px; font-size:.64rem; font-weight:750; }
    .temporary-test-modal { border:1px solid #cfd1d4; border-radius:5px; }
    .temporary-test-modal__eyebrow { display:block; margin-bottom:3px; color:#73777d; font-size:.56rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; }
    .temporary-test-modal__list { display:grid; gap:7px; margin:0 0 16px; padding-left:20px; color:#45494e; font-size:.72rem; }
    .temporary-test-modal__notice { padding:12px; background:#f1f1f0; border:1px solid #d5d6d8; border-left:4px solid #55595e; border-radius:3px; color:#4b4f54; font-size:.68rem; }
    @media (max-width: 767px) { .temporary-test-card { grid-template-columns:auto minmax(0,1fr); } .temporary-test-card__action { grid-column:1 / -1; } .temporary-test-card__action .btn, .temporary-test-card__action form { width:100%; } }
    .store-control-card { border: 1px solid #e4e8ef; border-radius: 8px; padding: 1.15rem; background: #fff; }
    .store-control-icon { width: 42px; height: 42px; border-radius: 7px; display: inline-flex; align-items: center; justify-content: center; flex: 0 0 42px; }
    .store-control-card .form-check-input { width: 2.8rem; height: 1.45rem; cursor: pointer; }
    .store-profile-card { display:flex; align-items:center; justify-content:space-between; gap:1.5rem; border:1px solid #dce3ec; border-radius:8px; padding:1.15rem; background:#f8fafc; }
    .store-profile-select { max-width: 320px; }
    .store-nav-toggle { border:1px solid #e4e8ef; border-radius:8px; padding:1rem 1.1rem; background:#fff; min-height:64px; }
    @media (max-width: 575px) { .store-profile-card { align-items:stretch; flex-direction:column; } .store-profile-select { max-width:none; } }
</style>
@if($temporaryTestActive && $temporaryTestExpiresAt)
<script>
document.addEventListener('DOMContentLoaded', function () {
    const card = document.querySelector('[data-temporary-test-expires]');
    const countdown = card?.querySelector('[data-temporary-test-countdown]');
    if (!card || !countdown) return;

    const expiresAt = new Date(card.dataset.temporaryTestExpires).getTime();
    const update = function () {
        const seconds = Math.max(0, Math.ceil((expiresAt - Date.now()) / 1000));
        const minutes = String(Math.floor(seconds / 60)).padStart(2, '0');
        const remainder = String(seconds % 60).padStart(2, '0');
        countdown.textContent = `${minutes}:${remainder}`;
        if (seconds === 0) window.location.reload();
    };

    update();
    window.setInterval(update, 1000);
});
</script>
@endif

@endsection
