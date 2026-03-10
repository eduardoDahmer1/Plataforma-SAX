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
                        <tr>
                            <td class="horarios-dia">Segunda-feira</td>
                            <td class="horarios-hora horarios-fechado">Fechado</td>
                        </tr>
                        <tr>
                            <td class="horarios-dia">Terça — Quinta</td>
                            <td class="horarios-hora">09:00 — 23:00</td>
                        </tr>
                        <tr>
                            <td class="horarios-dia">Sexta — Sábado</td>
                            <td class="horarios-hora">09:00 — 23:30</td>
                        </tr>
                        <tr>
                            <td class="horarios-dia">Domingo</td>
                            <td class="horarios-hora">09:00 — 23:00</td>
                        </tr>
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

                {{-- Placeholder mapa — substituir por iframe quando tiver endereço --}}
                <div class="img-placeholder rounded mb-4" style="height: 18.75rem; background: #2a3d5e;">
                    MAPA
                </div>

                {{-- Endereço placeholder --}}
                <address class="horarios-endereco">
                    <i class="bi bi-geo-alt me-2"></i>
                    Shopping Dubai, Pedro Juan Caballero — Paraguai
                </address>
            </div>

        </div>
    </div>
</section>