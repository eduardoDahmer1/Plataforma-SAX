<div class="sax-wrapper">
<section class="help-section">
    @if(filled($sectionContent['title']) || filled($sectionContent['description']))
        <header class="sax-home-section-heading">
            @if(filled($sectionContent['title']))<h2>{{ $sectionContent['title'] }}</h2>@endif
            @if(filled($sectionContent['description']))<p>{{ $sectionContent['description'] }}</p>@endif
        </header>
    @endif
    <div class="help-grid">
        <div class="help-card">
            <div class="icon">
                @if ($attribute && $attribute->icon_cabide && Storage::disk('public')->exists('uploads/' . $attribute->icon_cabide))
                    <img src="{{ asset('storage/uploads/' . $attribute->icon_cabide) }}" alt="Guia" width="30">
                @else 👕 @endif
            </div>
            <h3>{{ __('messages.como_realizar_compra') }}</h3>
            <p>{{ __('messages.guia_fazer_pedidos') }}</p>
        </div>
        <div class="help-card">
            <div class="icon">
                @if ($attribute && $attribute->icon_help && Storage::disk('public')->exists('uploads/' . $attribute->icon_help))
                    <img src="{{ asset('storage/uploads/' . $attribute->icon_help) }}" alt="FAQ" width="30">
                @else <span class="red-icon">?</span> @endif
            </div>
            <h3>{{ __('messages.perguntas_frequentes') }}</h3>
            <p>{{ __('messages.respondemos_duvidas') }}</p>
        </div>
        <div class="help-card">
            <div class="icon">
                @if ($attribute && $attribute->icon_info && Storage::disk('public')->exists('uploads/' . $attribute->icon_info))
                    <img src="{{ asset('storage/uploads/' . $attribute->icon_info) }}" alt="Ajuda" width="30">
                @else ⓘ @endif
            </div>
            <h3>{{ __('messages.precisa_ajuda') }}</h3>
            <p>{{ __('messages.fale_com_equipe') }}</p>
        </div>
    </div>
</section>
</div>
