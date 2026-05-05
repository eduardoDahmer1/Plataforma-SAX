@extends('layout.dashboard')

@section('content')
<div class="sax-dashboard-wrapper">
    {{-- Cabeçalho de Boas-vindas --}}
    <div class="dashboard-header mb-5">
        <h1 class="sax-title">{{ __('messages.ola') }}, {{ explode(' ', auth()->user()->name)[0] }}</h1>
        <p class="sax-subtitle">{{ __('messages.gerencie_infos') }}</p>
        <div class="sax-divider-black"></div>
    </div>

    {{-- Perfil e Dados --}}
    <div class="section-label mb-3">
        <h6 class="sax-section-title">{{ __('messages.infos_conta') }}</h6>
    </div>

    <div class="row g-3 mb-5">
        @php
            $u = auth()->user();
            $fullAddress = $u->address;
            if($u->number) $fullAddress .= ', ' . $u->number;
            if($u->complement) $fullAddress .= ' (' . $u->complement . ')';
            if($u->district) $fullAddress .= ' - ' . $u->district;

            $fields = [
                ['label' => __('messages.label_nome'), 'value' => $u->name, 'icon' => 'user'],
                ['label' => __('messages.label_email'), 'value' => $u->email, 'icon' => 'envelope'],
                ['label' => __('messages.label_telefone'), 'value' => ($u->phone_country ? '+'.$u->phone_country : '') . ' ' . $u->phone_number, 'icon' => 'phone'],
                ['label' => __('messages.label_documento'), 'value' => $u->document, 'icon' => 'id-card'],
                ['label' => __('messages.label_endereco'), 'value' => $fullAddress, 'icon' => 'home'],
                ['label' => __('messages.label_cidade_estado'), 'value' => ($u->city && $u->state) ? $u->city . ' - ' . $u->state : $u->city . $u->state, 'icon' => 'map-marker-alt'],
                ['label' => __('messages.label_cep') ?? 'CEP', 'value' => $u->cep, 'icon' => 'mail-bulk'],
                ['label' => __('messages.label_pais') ?? 'País', 'value' => ucfirst($u->country), 'icon' => 'globe'],
            ];
        @endphp

        @foreach($fields as $field)
            @if(trim($field['value']))
            <div class="col-12 col-md-4">
                <div class="sax-info-card">
                    <div class="card-icon-minimal">
                        <i class="fas fa-{{ $field['icon'] }}"></i>
                    </div>
                    <div class="card-details">
                        <span class="label">{{ $field['label'] }}</span>
                        <div class="value" title="{{ $field['value'] }}">{{ $field['value'] }}</div>
                    </div>
                </div>
            </div>
            @endif
        @endforeach
    </div>

    {{-- Seção de Pedidos --}}
    <div class="section-label d-flex justify-content-between align-items-end mb-4">
        <h6 class="sax-section-title m-0">{{ __('messages.pedidos_recentes') }}</h6>
        @if ($orders->count() > 0)
            <a href="{{ route('user.orders') }}" class="btn-link-sax">
                {{ __('messages.ver_historico') }} <i class="fas fa-chevron-right ms-1"></i>
            </a>
        @endif
    </div>

    @if ($orders->count())
        <div class="order-container mb-5">
            @foreach ($orders->take(5) as $order)
                <div class="order-card-sax shadow-sm">
                    <div class="order-content">
                        <div class="order-block">
                            <span class="sax-label-min">{{ __('messages.num_pedido') }}</span>
                            <span class="order-id">#{{ $order->id }}</span>
                            <span class="order-date">{{ $order->created_at->format('d/m/Y') }}</span>
                        </div>
                        <div class="order-block">
                            <span class="sax-label-min">{{ __('messages.pagamento') }}</span>
                            <span class="badge-payment">{{ ucfirst($order->payment_method) }}</span>
                        </div>
                        <div class="order-block">
                            <span class="sax-label-min">{{ __('messages.status') }}</span>
                            @php($status = strtolower((string) $order->status))
                            <div class="status-indicator {{ $status }}">
                                <span class="dot"></span> 
                                @switch($status)
                                    @case('pending') {{ __('messages.status_pending') }} @break
                                    @case('processing') {{ __('messages.status_processing') }} @break
                                    @case('completed') {{ __('messages.status_completed') }} @break
                                    @case('paid') {{ __('messages.status_paid') }} @break
                                    @case('failed') {{ __('messages.status_failed') }} @break
                                    @case('canceled') 
                                    @case('cancelled') {{ __('messages.status_canceled') }} @break
                                    @default {{ __('messages.status_unknown') }}
                                @endswitch
                            </div>
                        </div>
                        <div class="order-action">
                            <a href="{{ route('user.orders.show', $order->id) }}" class="btn-sax-black">{{ __('messages.detalhes') }}</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state mb-5">
            <i class="fas fa-box-open fa-2x mb-3 opacity-50"></i>
            <p>{{ __('messages.sem_pedidos') }}</p>
        </div>
    @endif

<div class="section-label mb-3 mt-5">
    <h6 class="sax-section-title">VISTOS RECENTEMENTE</h6>
</div>

@if(isset($userHistory) && $userHistory->count() > 0)
    <div class="history-slider-wrapper" style="overflow: hidden;">
        <div class="swiper historySwiper">
            <div class="swiper-wrapper">
                @foreach($userHistory as $item)
                    <div class="swiper-slide" style="width: 200px;"> {{-- Largura inicial para evitar 'gigantismo' --}}
                        @include('home-components.product-card', ['item' => $item])
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Pequeno delay para garantir que o arquivo swiper-bundle.min.js foi lido
            setTimeout(function() {
                if (typeof Swiper !== 'undefined') {
                    new Swiper(".historySwiper", {
                        slidesPerView: 2,
                        spaceBetween: 10,
                        breakpoints: {
                            640: { slidesPerView: 3 },
                            1024: { slidesPerView: 5 },
                            1400: { slidesPerView: 5 }
                        }
                    });
                } else {
                    console.error("Swiper não carregado! Verifique o arquivo de scripts master.");
                }
            }, 200);
        });
    </script>
@endif

</div>
@endsection