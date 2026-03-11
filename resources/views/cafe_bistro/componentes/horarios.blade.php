{{-- Tarefa 8: Horários + Localização --}}
<section id="horarios" class="section-padding" style="background: var(--azul-profundo);">
    <div class="container">
        <div class="row gy-5">

            {{-- Horários --}}
            <div class="col-lg-6" data-reveal="left">
                <span class="eyebrow">Funcionamento</span>
                <div class="divider"></div>
                <h2 class="section-title mb-4">Horários</h2>

                <table class="horarios-table w-100">
                    <tbody>
                        @foreach($cafeBistro->horarios ?? [] as $h)
                            <tr>
                                <td class="horarios-dia">{{ $h['dia'] }}</td>
                                <td class="horarios-hora {{ !$h['apertura'] ? 'horarios-fechado' : '' }}">
                                    {{ $h['apertura'] ? $h['apertura'] . ' — ' . $h['cierre'] : 'Fechado' }}
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