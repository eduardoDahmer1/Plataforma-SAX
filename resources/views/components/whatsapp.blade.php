@php
    if (!isset($whatsapp_banner) || !$whatsapp_banner) {
        $attributeSetting = \App\Models\Attribute::find(1);
        $whatsapp_banner = $attributeSetting ? $attributeSetting->whatsapp_banner : null;
    }
@endphp

@if($whatsapp_banner)
@php
    // Definição das opções usando chaves puras (sem tradução no índice)
    if (Request::is('*bridal*')) {
        $sectionTitle = __('messages.sax_bridal_atendimento_exclusivo');
        $phone = '+595 981 527848';
        $options = [
            'agendar_atendimento_privativo'      => __('messages.msg_agendar_atendimento_privativo'),
            'alta_costura_internacional'         => __('messages.msg_alta_costura_internacional'),
            'alfaiataria_e_ajustes_sob_medida'   => __('messages.msg_alfaiataria_e_ajustes_sob_medida'),
            'agendar_prova_com_convidados'       => __('messages.msg_agendar_prova_com_convidados'),
            'catalogo_e_colecoes_atuais'         => __('messages.msg_catalogo_e_colecoes_atuais')
        ];
    } elseif (Request::is('*cafe*') || Request::is('*bistro*')) {
        $sectionTitle = __('messages.cafe_bistro_reservas_experiencias');
        $phone = '+595 984 167575';
        $options = [
            'menu_do_dia_e_gastronomia'          => __('messages.msg_menu_do_dia_e_gastronomia'),
            'reservar_uma_mesa'                  => __('messages.msg_reservar_uma_mesa'),
            'eventos_e_catering_privativo'       => __('messages.msg_eventos_e_catering_privativo'),
            'horarios_de_funcionamento'          => __('messages.msg_horarios_de_funcionamento'),
            'confeitaria_e_encomendas_finas'     => __('messages.msg_confeitaria_e_encomendas_finas')
        ];
    } elseif (Request::is('*palace*')) {
        $sectionTitle = __('messages.sax_palace_eventos_de_excelencia');
        $phone = '+595 981 528186';
        $options = [
            'consultoria_para_grandes_eventos'   => __('messages.msg_consultoria_para_grandes_eventos'),
            'consulta_de_disponibilidade'        => __('messages.msg_consulta_de_disponibilidade'),
            'agendar_visita_tecnica'             => __('messages.msg_agendar_visita_tecnica'),
            'orcamento_personalizado'            => __('messages.msg_orcamento_personalizado'),
            'capacidade_e_layouts'               => __('messages.msg_capacidade_e_layouts')
        ];
    } elseif (Route::is('checkout.*')) {
        $sectionTitle = __('messages.suporte_premium_ao_checkout');
        $phone = '+595 984 167575';
        $options = [
            'assistencia_imediata'               => __('messages.msg_assistencia_imediata'),
            'suporte_tecnico_de_pagamento'       => __('messages.msg_suporte_tecnico_de_pagamento'),
            'confirmar_aprovacao_de_pedido'      => __('messages.msg_confirmar_aprovacao_de_pedido'),
            'duvidas_fiscais_e_faturamento'      => __('messages.msg_duvidas_fiscais_e_faturamento'),
            'entrega_e_prazos_logisticos'        => __('messages.msg_entrega_e_prazos_logisticos')
        ];
    } else {
        $sectionTitle = __('messages.concierge_digital_sax');
        $phone = '+595 984 167575';
        $options = [
            'consultoria_de_produto'             => __('messages.msg_consultoria_de_produto'),
            'informacoes_de_envio_e_frete'       => __('messages.msg_informacoes_de_envio_e_frete'),
            'servico_de_personal_shopper'        => __('messages.msg_servico_de_personal_shopper'),
            'trocas_e_pos_venda'                 => __('messages.msg_trocas_e_pos_venda'),
            'rastrear_pedido'                    => __('messages.msg_rastrear_pedido')
        ];
    }
@endphp

    <!-- O restante do HTML permanece igual -->
    <div class="whatsapp-container" id="whatsappContainer">
        <div class="whatsapp-menu" id="whatsappMenu">
            <div class="whatsapp-menu-header">
                <strong>{{ $sectionTitle }}</strong>
                <span>Como podemos te ajudar hoje?</span>
            </div>
                <div class="whatsapp-menu-body">
                    @foreach($options as $labelKey => $message)
                        @php
                            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
                        @endphp
                        <a href="https://wa.me/{{ $cleanPhone }}?text={{ urlencode($message) }}" target="_blank" rel="noopener noreferrer" class="whatsapp-menu-item">
                            <i class="fab fa-whatsapp"></i> {{ __('messages.' . $labelKey) }}
                        </a>
                    @endforeach
                </div>
        </div>

        <button type="button" class="whatsapp-floating-banner" id="whatsappToggle">
            <img src="{{ asset('storage/uploads/' . $whatsapp_banner) }}" alt="WhatsApp">
        </button>
    </div>

    <style>
        .whatsapp-container {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 2000;
        }

        .whatsapp-floating-banner {
            background: none !important;
            background-color: transparent !important;
            border: none !important;
            display: block;
            width: 50px;
            height: 50px;
            padding: 0;
            cursor: pointer;
            transition: transform 0.3s ease, filter 0.3s ease;
            filter: drop-shadow(0px 4px 10px rgba(0, 0, 0, 0.15));
        }

        .whatsapp-floating-banner img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            background: none !important;
            background-color: transparent !important;
        }

        .whatsapp-floating-banner:hover {
            transform: scale(1.1);
            filter: drop-shadow(0px 6px 14px rgba(0, 0, 0, 0.25));
        }

        .whatsapp-menu {
            position: absolute;
            bottom: 65px;
            right: 0;
            width: 290px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0px 5px 20px rgba(0, 0, 0, 0.15);
            display: none;
            opacity: 0;
            transform: translateY(10px);
            transition: opacity 0.3s ease, transform 0.3s ease;
            overflow: hidden;
            border: 1px solid #f1f1f1;
        }

        /* Classe de controle ativa por JavaScript */
        .whatsapp-container.show-menu .whatsapp-menu {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }

        .whatsapp-menu-header {
            background-color: #000000;
            color: #ffffff;
            padding: 12px 15px;
            display: flex;
            flex-direction: column;
        }

        .whatsapp-menu-header strong {
            font-size: 14px;
            letter-spacing: 0.5px;
            font-family: 'Montserrat', sans-serif;
        }

        .whatsapp-menu-header span {
            font-size: 11px;
            color: #b5b5b5;
            margin-top: 2px;
        }

        .whatsapp-menu-body {
            padding: 8px 0;
            background-color: #fff;
        }

        .whatsapp-menu-item {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            color: #333333 !important;
            text-decoration: none !important;
            font-size: 12px;
            font-weight: 400;
            line-height: 1.4;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .whatsapp-menu-item i {
            color: #25D366;
            font-size: 15px;
            margin-right: 10px;
            flex-shrink: 0;
        }

        .whatsapp-menu-item:hover {
            background-color: #f7f7f7;
            color: #e6c200 !important;
        }

        @media (max-width: 768px) {
            .whatsapp-container {
                bottom: 20px;
                right: 20px;
            }

            .whatsapp-floating-banner {
                width: 45px;
                height: 45px;
            }

            .whatsapp-menu {
                width: 260px;
                bottom: 55px;
            }
        }
    </style>

    <script>
        if (typeof whatsappScriptLoaded === 'undefined') {
            var whatsappScriptLoaded = true;

            document.addEventListener('DOMContentLoaded', function () {
                const container = document.getElementById('whatsappContainer');
                const toggleBtn = document.getElementById('whatsappToggle');
                let closeTimeout = null;

                if (container && toggleBtn) {
                    // Ao passar o mouse: Abre imediatamente e limpa qualquer timer ativo
                    container.addEventListener('mouseenter', function () {
                        if (window.innerWidth > 768) {
                            clearTimeout(closeTimeout);
                            container.classList.add('show-menu');
                        }
                    });

                    // Ao tirar o mouse: Inicia o timer para fechar após 5 segundos (5000ms)
                    container.addEventListener('mouseleave', function () {
                        if (window.innerWidth > 768) {
                            closeTimeout = setTimeout(function () {
                                container.classList.remove('show-menu');
                        }, 5000); 
                    }
                });

                    // Suporte para Click (Mobile & Fallback)
                    toggleBtn.addEventListener('click', function (e) {
                        if (window.innerWidth <= 768) {
                            e.preventDefault();
                            container.classList.toggle('show-menu');
                    } else {
                            clearTimeout(closeTimeout);
                            container.classList.toggle('show-menu');
                        }
                    });

                    // Fechar ao clicar fora do componente
                    document.addEventListener('click', function (e) {
                        if (!container.contains(e.target)) {
                            container.classList.remove('show-menu');
                            clearTimeout(closeTimeout);
                        }
                    });
                }
            });
        }
    </script>
@endif