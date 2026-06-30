@php
    if (!isset($whatsapp_banner) || !$whatsapp_banner) {
        $attributeSetting = \App\Models\Attribute::find(1);
        $whatsapp_banner = $attributeSetting ? $attributeSetting->whatsapp_banner : null;
    }
@endphp

@if($whatsapp_banner)
@php
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

    $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
@endphp

    <div class="whatsapp-container" id="whatsappContainer">
        <div class="whatsapp-menu" id="whatsappMenu">
            <div class="whatsapp-menu-header">
                <strong>{{ $sectionTitle }}</strong>
                <span>Escolha uma opcao e fale com nosso time.</span>
            </div>
            <div class="whatsapp-menu-body">
                @foreach($options as $labelKey => $message)
                    <a href="https://wa.me/{{ $cleanPhone }}?text={{ urlencode($message) }}" target="_blank" rel="noopener noreferrer" class="whatsapp-menu-item" aria-label="{{ __('messages.' . $labelKey) }}">
                        <i class="fab fa-whatsapp" aria-hidden="true"></i>
                        <span>{{ __('messages.' . $labelKey) }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <button type="button" class="whatsapp-floating-banner" id="whatsappToggle" aria-expanded="false" aria-controls="whatsappMenu" aria-label="Abrir atendimento no WhatsApp">
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
            background: transparent;
            border: 0;
            display: block;
            width: 54px;
            height: 54px;
            padding: 0;
            cursor: pointer;
            border-radius: 999px;
            transition: transform 0.24s ease, filter 0.24s ease;
            filter: drop-shadow(0 6px 14px rgba(0, 0, 0, 0.2));
            will-change: transform;
        }

        .whatsapp-floating-banner img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
            border-radius: 999px;
        }

        .whatsapp-floating-banner:hover {
            transform: translateY(-2px) scale(1.03);
            filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.28));
        }

        .whatsapp-menu {
            position: absolute;
            bottom: 72px;
            right: 0;
            width: 320px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 16px 42px rgba(0, 0, 0, 0.2);
            display: none;
            opacity: 0;
            transform: translateY(12px) scale(0.98);
            transition: opacity 0.2s ease, transform 0.2s ease;
            overflow: hidden;
            border: 1px solid #e8e8e8;
            backdrop-filter: blur(2px);
        }

        .whatsapp-container.show-menu .whatsapp-menu {
            display: block;
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        .whatsapp-menu-header {
            background: linear-gradient(135deg, #111111 0%, #2a2a2a 100%);
            color: #fff;
            padding: 13px 15px;
            display: flex;
            flex-direction: column;
        }

        .whatsapp-menu-header strong {
            font-size: 14px;
            letter-spacing: 0.5px;
            font-family: 'Montserrat', sans-serif;
            line-height: 1.2;
        }

        .whatsapp-menu-header span {
            font-size: 11px;
            color: #d4d4d4;
            margin-top: 5px;
            line-height: 1.3;
        }

        .whatsapp-menu-body {
            padding: 6px 0;
            background-color: #fff;
            max-height: min(52vh, 360px);
            overflow-y: auto;
        }

        .whatsapp-menu-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 15px;
            color: #333333 !important;
            text-decoration: none !important;
            font-size: 12px;
            font-weight: 500;
            line-height: 1.4;
            transition: background-color 0.16s ease, color 0.16s ease;
        }

        .whatsapp-menu-item i {
            color: #25D366;
            font-size: 16px;
            flex-shrink: 0;
        }

        .whatsapp-menu-item span {
            display: block;
            letter-spacing: 0.1px;
        }

        .whatsapp-menu-item:hover {
            background-color: #f4f4f4;
            color: #191919 !important;
        }

        @media (max-width: 768px) {
            .whatsapp-container {
                bottom: 16px;
                right: 14px;
            }

            .whatsapp-floating-banner {
                width: 48px;
                height: 48px;
            }

            .whatsapp-menu {
                width: min(320px, calc(100vw - 24px));
                bottom: 62px;
            }
        }
    </style>

    <script>
        (function () {
            var init = function () {
                var container = document.getElementById('whatsappContainer');
                var toggleBtn = document.getElementById('whatsappToggle');

                if (!container || !toggleBtn || container.dataset.bound === '1') {
                    return;
                }

                container.dataset.bound = '1';

                var closeTimer = null;
                var desktopMedia = window.matchMedia('(min-width: 769px)');

                var setOpen = function (open) {
                    container.classList.toggle('show-menu', open);
                    toggleBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
                };

                var clearCloseTimer = function () {
                    if (closeTimer) {
                        window.clearTimeout(closeTimer);
                        closeTimer = null;
                    }
                };

                var scheduleClose = function () {
                    clearCloseTimer();
                    closeTimer = window.setTimeout(function () {
                        setOpen(false);
                    }, 1800);
                };

                container.addEventListener('mouseenter', function () {
                    if (!desktopMedia.matches) return;
                    clearCloseTimer();
                    setOpen(true);
                });

                container.addEventListener('mouseleave', function () {
                    if (!desktopMedia.matches) return;
                    scheduleClose();
                });

                toggleBtn.addEventListener('click', function () {
                    clearCloseTimer();
                    setOpen(!container.classList.contains('show-menu'));
                });

                document.addEventListener('pointerdown', function (event) {
                    if (!container.contains(event.target)) {
                        clearCloseTimer();
                        setOpen(false);
                    }
                }, { passive: true });

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape') {
                        clearCloseTimer();
                        setOpen(false);
                    }
                });
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init, { once: true });
            } else {
                init();
            }
        })();
    </script>
@endif