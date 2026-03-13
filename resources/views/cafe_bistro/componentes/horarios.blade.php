{{-- Tarefa 8: Horários + Localização --}}
<section id="horarios" class="section-padding" style="background: var(--azul-profundo);">
    <div class="container">
        <div class="row gy-5">

            {{-- Horários --}}
            <div class="col-lg-6" data-reveal="left">
                <span class="eyebrow">Funcionamento</span>
                <div class="divider"></div>
                <h2 class="section-title mb-4">Horários</h2>

                @php
                    // Agrupar días consecutivos con el mismo horario
                    $horarios = $cafeBistro->horarios ?? [];
                    $grupos = [];
                    foreach ($horarios as $h) {
                        $abertura = $h['apertura'] ?? '';
                        $cierre   = $h['cierre']   ?? '';
                        $horario  = $abertura ? "$abertura — $cierre" : 'Fechado';
                        $ultimo   = count($grupos) - 1;
                        if ($ultimo >= 0 && $grupos[$ultimo]['horario'] === $horario) {
                            $grupos[$ultimo]['fim'] = $h['dia'] ?? '';
                        } else {
                            $grupos[] = [
                                'inicio'  => $h['dia'] ?? '',
                                'fim'     => '',
                                'horario' => $horario,
                                'fechado' => !$abertura,
                            ];
                        }
                    }
                @endphp
                <table class="horarios-table w-100">
                    <tbody>
                        @foreach($grupos as $g)
                            <tr>
                                <td class="horarios-dia">
                                    {{ $g['inicio'] }}{{ $g['fim'] ? ' — ' . $g['fim'] : '' }}
                                </td>
                                <td class="horarios-hora {{ $g['fechado'] ? 'horarios-fechado' : '' }}">
                                    {{ $g['horario'] }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <p class="horarios-nota mt-4">
                    * Recepção de pedidos até 30 min antes do fechamento
                </p>
            </div>

            {{-- Localização --}}
            <div class="col-lg-6" data-reveal="right">
                <span class="eyebrow">Localização</span>
                <div class="divider"></div>
                <h2 class="section-title mb-4">Onde estamos</h2>

                {{-- Mapa --}}
                @if($cafeBistro->has_mapa)
                    <div class="rounded mb-4" style="height: 18.75rem; overflow: hidden;">
                        {!! $cafeBistro->mapa_embed !!}
                    </div>
                @else
                    <div class="img-placeholder rounded mb-4" style="height: 18.75rem; background: #2a3d5e;">
                        MAPA
                    </div>
                @endif

                {{-- Endereço --}}
                <address class="horarios-endereco">
                    <i class="bi bi-geo-alt me-2"></i>
                    {{ $cafeBistro->direccion ?? 'Shopping Dubai, Pedro Juan Caballero — Paraguai' }}
                </address>
            </div>

        </div>
    </div>
</section>