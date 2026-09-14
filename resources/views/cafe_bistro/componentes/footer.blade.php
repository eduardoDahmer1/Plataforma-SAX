{{-- Tarefa 10: Footer --}}
<footer class="footer-cafe">
    <div class="container">

        <div class="row gy-5">

            {{-- Col 1: Logo + Descrição + Redes --}}
            <div class="col-lg-4 col-md-12">
                {{-- Logo --}}
                @php
                    $footerBistroLogo = ($cafeBistro->slug ?? null) === 'asuncion'
                        ? ($attributes->logo_cafe_bistro_asuncion ?? $attributes->logo_cafe_bistro ?? null)
                        : ($attributes->logo_cafe_bistro ?? null);
                @endphp
                <div class="mb-3">
                    @if(!empty($footerBistroLogo))
                        <img src="{{ asset('storage/uploads/' . $footerBistroLogo) }}"
                             alt="SAX Café & Bistrô" style="height:3.5rem;width:auto;">
                    @else
                        <div class="footer-logo-placeholder">
                            SAX <span>Café & Bistrô</span>
                        </div>
                    @endif
                </div>

                <p class="footer-descricao">{{ __('messages.cafe_footer_description') }}</p>

                <div class="footer-social">
                    @if($cafeBistro->instagram_url)
                        <a href="{{ $cafeBistro->instagram_url }}" target="_blank" rel="noopener" aria-label="Instagram" class="footer-social-link">
                            <i class="bi bi-instagram"></i>
                        </a>
                    @endif
                    @if($cafeBistro->facebook_url)
                        <a href="{{ $cafeBistro->facebook_url }}" target="_blank" rel="noopener" aria-label="Facebook" class="footer-social-link">
                            <i class="bi bi-facebook"></i>
                        </a>
                    @endif
                    <a href="{{ $cafeBistro->whatsapp_link }}" target="_blank" rel="noopener" aria-label="WhatsApp" class="footer-social-link">
                        <i class="bi bi-whatsapp"></i>
                    </a>
                </div>
            </div>

            {{-- Col 2: Horários resumidos --}}
            <div class="col-lg-4 col-md-6">
                <h6 class="footer-col-title">{{ __('messages.horarios') }}</h6>

                @php
                    $footerDiasLabels = [
                        'segunda' => __('messages.cafe_monday'),
                        'terca'   => __('messages.cafe_tuesday'),
                        'quarta'  => __('messages.cafe_wednesday'),
                        'quinta'  => __('messages.cafe_thursday'),
                        'sexta'   => __('messages.cafe_friday'),
                        'sabado'  => __('messages.cafe_saturday'),
                        'domingo' => __('messages.cafe_sunday'),
                    ];
                    $footerGrupos = $cafeBistro->horariosAgrupados();
                @endphp
                <table class="footer-horarios w-100">
                    <tbody>
                        @foreach($footerGrupos as $grupo)
                            <tr>
                                <td class="footer-dia">
                                    @if($grupo['inicio_dia'] === $grupo['fim_dia'])
                                        {{ $footerDiasLabels[$grupo['inicio_dia']] }}
                                    @else
                                        {{ $footerDiasLabels[$grupo['inicio_dia']] }} — {{ $footerDiasLabels[$grupo['fim_dia']] }}
                                    @endif
                                </td>
                                <td class="footer-hora">
                                    @if($grupo['aberto'])
                                        {{ $grupo['inicio'] }} — {{ $grupo['fim'] }}
                                    @else
                                        {{ __('messages.cafe_closed') }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Col 3: Endereço + Telefone + Reserva --}}
            <div class="col-lg-4 col-md-6">
                <h6 class="footer-col-title">{{ __('messages.cafe_location_contact') }}</h6>

                <address class="footer-endereco">
                    <i class="bi bi-geo-alt me-2"></i>
                    {{ $t?->cafe_direccion ?? $cafeBistro->direccion ?? __('messages.cafe_address_fallback') }}
                </address>

                <p class="footer-telefone">
                    <i class="bi bi-telephone me-2"></i>
                    {{ $cafeBistro->telefono ?? '+595 993 011502' }}
                </p>

                <a href="{{ $cafeBistro->whatsapp_link }}" target="_blank" rel="noopener" class="btn-reservar-cafe d-inline-block mt-2">
                    {{ __('messages.cafe_make_reservation') }}
                </a>
            </div>

        </div>

        {{-- Linha inferior --}}
        <div class="footer-bottom">
            <p class="footer-copyright">
                &copy; {{ date('Y') }} SAX Café &amp; Bistrô &middot; {{ __('messages.direitos_reservados') }}
                &middot; <a href="{{ route('policies.index') }}" class="text-reset">{{ __('messages.policies_and_terms') }}</a>
            </p>
        </div>

    </div>
</footer>
