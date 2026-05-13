<div class="sax-wrapper">
    <section class="help-section">
        <div class="help-grid">
            <div class="help-card">
                <div class="icon">
                    @if ($attribute && $attribute->icon_cabide && Storage::disk('public')->exists('uploads/' .
                    $attribute->icon_cabide))
                    <img src="{{ asset('storage/uploads/' . $attribute->icon_cabide) }}" alt="Guia" width="30">
                    @else 👕 @endif
                </div>
                <h3>{{ __('messages.como_realizar_compra') }}</h3>
                <p>{{ __('messages.guia_fazer_pedidos') }}</p>
            </div>

            <div class="help-card">
                <div class="icon">
                    @if ($attribute && $attribute->icon_help && Storage::disk('public')->exists('uploads/' .
                    $attribute->icon_help))
                    <img src="{{ asset('storage/uploads/' . $attribute->icon_help) }}" alt="FAQ" width="30">
                    @else <span class="red-icon">?</span> @endif
                </div>
                <h3>{{ __('messages.perguntas_frequentes') }}</h3>
                <p>{{ __('messages.respondemos_duvidas') }}</p>
            </div>

            <div class="help-card">
                <div class="icon">
                    @if ($attribute && $attribute->icon_info && Storage::disk('public')->exists('uploads/' .
                    $attribute->icon_info))
                    <img src="{{ asset('storage/uploads/' . $attribute->icon_info) }}" alt="Ajuda" width="30">
                    @else ⓘ @endif
                </div>
                <h3>{{ __('messages.precisa_ajuda') }}</h3>
                <p>{{ __('messages.fale_com_equipe') }}</p>
            </div>
        </div>
    </section>

    <section class="newsletter-section"
        style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('{{ asset('storage/uploads/' . ($banner1 ?? 'banner1.webp')) }}');">
        <div class="newsletter-container">
            <h2>{{ __('messages.nao_perca_novidade') }}</h2>
            <p class="subtitle">{{ __('messages.registro_promo_texto') }}</p>

            <div class="form-wrapper">
                <form class="newsletter-form">
                    <input type="email" placeholder="{{ __('messages.seu_email') }}" required>
                    <button type="submit">{{ __('messages.inscrever_se') }}</button>
                </form>
                <p class="legal-text">
                    {{ __('messages.prazos_entrega_texto') }}
                </p>
            </div>
        </div>
    </section>
</div>
