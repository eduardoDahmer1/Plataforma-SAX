@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="Controle operacional da loja"
        description="Ative ou pause recursos da loja imediatamente, sem alterar código ou credenciais." />
    <x-admin.alert />

    <form method="POST" action="{{ route('admin.store-controls.update') }}" class="mt-4">
        @csrf
        @method('PUT')

        <div class="alert alert-warning border-0 mb-4">
            <strong><i class="fa-solid fa-triangle-exclamation me-2"></i>Atenção:</strong>
            estas chaves entram em vigor assim que você guardar. Pedidos e pagamentos já criados continuam acessíveis para não interromper uma cobrança pendente.
        </div>

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

        <div class="border-top pt-4 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
            <small class="text-muted"><i class="fa-solid fa-shield-halved me-1"></i>Somente Admin Master pode alterar estes controles.</small>
            <button class="btn btn-dark px-5 py-2 text-uppercase fw-bold" type="submit">
                <i class="fa-solid fa-floppy-disk me-2"></i>Guardar controles
            </button>
        </div>
    </form>
</x-admin.card>

<style>
    .store-control-card { border: 1px solid #e4e8ef; border-radius: 8px; padding: 1.15rem; background: #fff; }
    .store-control-icon { width: 42px; height: 42px; border-radius: 7px; display: inline-flex; align-items: center; justify-content: center; flex: 0 0 42px; }
    .store-control-card .form-check-input { width: 2.8rem; height: 1.45rem; cursor: pointer; }
</style>
@endsection
