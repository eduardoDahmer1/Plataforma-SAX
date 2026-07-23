@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header title="SEO e Marketing" description="Metadados globais, verificações, pixels e integrações das páginas públicas." />
    <x-admin.alert />

    <form method="POST" action="{{ route('admin.marketing.update') }}" class="mt-4">
        @csrf
        @method('PUT')

        <div class="alert alert-warning border-0">Códigos personalizados executam JavaScript real em todas as páginas públicas. Use somente códigos de serviços confiáveis.</div>
        <div class="form-check form-switch mb-4">
            <input type="hidden" name="enabled" value="0">
            <input class="form-check-input" type="checkbox" name="enabled" value="1" id="enabled" @checked(old('enabled', $settings->enabled))>
            <label class="form-check-label fw-bold" for="enabled">Ativar SEO e integrações de marketing</label>
        </div>

        <h2 class="h6 text-uppercase fw-bold border-bottom pb-2 mb-3">SEO global</h2>
        <div class="row g-3 mb-5">
            <div class="col-md-6"><label class="sax-form-label">Nome do site</label><input class="form-control sax-input" name="site_name" value="{{ old('site_name', $settings->site_name) }}" placeholder="SAX Department"><small class="form-text text-muted">Nome oficial exibido para buscadores e redes sociais. Exemplo: SAX Department.</small></div>
            <div class="col-md-6"><label class="sax-form-label">Título padrão</label><input class="form-control sax-input" name="default_meta_title" value="{{ old('default_meta_title', $settings->default_meta_title) }}" maxlength="255"><small class="form-text text-muted">Título usado quando uma página não possui título próprio. Prefira aproximadamente 50 a 60 caracteres.</small></div>
            <div class="col-12"><label class="sax-form-label">Descrição padrão</label><textarea class="form-control sax-input" name="default_meta_description" rows="3" maxlength="500">{{ old('default_meta_description', $settings->default_meta_description) }}</textarea><small class="form-text text-muted">Resumo que pode aparecer no Google. Descreva a loja e seu diferencial em aproximadamente 140 a 160 caracteres.</small></div>
            <div class="col-12"><label class="sax-form-label">Palavras-chave</label><textarea class="form-control sax-input" name="default_meta_keywords" rows="2" maxlength="20000" placeholder="moda, luxo, departamento">{{ old('default_meta_keywords', $settings->default_meta_keywords) }}</textarea><small class="form-text text-muted">Termos relacionados ao negócio, separados por vírgulas. Use expressões relevantes e evite repetições desnecessárias.</small></div>
            @php($isProductionDomain = request()->getHost() === 'saxdepartment.com')
            <div class="col-md-6"><label class="sax-form-label">Robots — automático</label><input class="form-control sax-input" value="{{ $isProductionDomain ? 'index,follow' : 'noindex,nofollow' }}" readonly><small class="form-text text-muted">Em <strong>saxdepartment.com</strong>, permite a indexação. Em qualquer outro domínio, incluindo o stage, bloqueia automaticamente a indexação.</small></div>
            <div class="col-md-6"><label class="sax-form-label">URL canônica — automática</label><input class="form-control sax-input" value="https://saxdepartment.com" readonly><small class="form-text text-muted">Cada página aponta automaticamente para o mesmo caminho no domínio oficial <strong>saxdepartment.com</strong>.</small></div>
        </div>

        <h2 class="h6 text-uppercase fw-bold border-bottom pb-2 mb-3">Compartilhamento social</h2>
        <div class="row g-3 mb-5">
            <div class="col-md-6"><label class="sax-form-label">Título Open Graph</label><input class="form-control sax-input" name="og_title" value="{{ old('og_title', $settings->og_title) }}"><small class="form-text text-muted">Título mostrado quando o site é compartilhado no Facebook, WhatsApp, LinkedIn e outras redes.</small></div>
            <div class="col-md-6"><label class="sax-form-label">Imagem Open Graph</label><input type="url" class="form-control sax-input" name="og_image_url" value="{{ old('og_image_url', $settings->og_image_url) }}"><small class="form-text text-muted">URL pública completa da imagem de compartilhamento. Recomendação: 1200 × 630 pixels.</small></div>
            <div class="col-12"><label class="sax-form-label">Descrição Open Graph</label><textarea class="form-control sax-input" name="og_description" rows="3">{{ old('og_description', $settings->og_description) }}</textarea><small class="form-text text-muted">Texto de apresentação usado nos cartões de compartilhamento das redes sociais.</small></div>
            <div class="col-md-6"><label class="sax-form-label">Perfil X/Twitter</label><input class="form-control sax-input" name="twitter_site" value="{{ old('twitter_site', $settings->twitter_site) }}" placeholder="@saxdepartment"><small class="form-text text-muted">Usuário oficial no X/Twitter, incluindo ou não o símbolo @. Pode ficar vazio.</small></div>
        </div>

        <h2 class="h6 text-uppercase fw-bold border-bottom pb-2 mb-3">Verificação de buscadores</h2>
        <div class="row g-3 mb-5">
            <div class="col-md-6"><label class="sax-form-label">Google Search Console</label><input class="form-control sax-input font-monospace" name="google_site_verification" value="{{ old('google_site_verification', $settings->google_site_verification) }}" placeholder="Conteúdo da meta tag"><small class="form-text text-muted">Cole somente o valor do atributo <code>content</code> fornecido pelo Search Console, não a meta tag inteira.</small></div>
            <div class="col-md-6"><label class="sax-form-label">Bing Webmaster Tools</label><input class="form-control sax-input font-monospace" name="bing_site_verification" value="{{ old('bing_site_verification', $settings->bing_site_verification) }}" placeholder="Conteúdo da meta tag"><small class="form-text text-muted">Cole somente o código de verificação fornecido pelo Bing. Pode ficar vazio se o serviço não for utilizado.</small></div>
            <div class="col-md-6"><label class="sax-form-label">Verificação de domínio Meta</label><input class="form-control sax-input font-monospace" name="meta_domain_verification" value="{{ old('meta_domain_verification', $settings->meta_domain_verification) }}" placeholder="Conteúdo da meta tag"><small class="form-text text-muted">Código fornecido no Gerenciador de Negócios da Meta para confirmar a propriedade do domínio.</small></div>
        </div>

        <h2 class="h6 text-uppercase fw-bold border-bottom pb-2 mb-3">Analytics, anúncios e pixels</h2>
        <div class="row g-3 mb-5">
            <div class="col-md-6"><label class="sax-form-label">Google Tag Manager</label><input class="form-control sax-input font-monospace" name="google_tag_manager_id" value="{{ old('google_tag_manager_id', $settings->google_tag_manager_id) }}" placeholder="GTM-XXXXXXX"><small class="form-text text-muted">ID do contêiner encontrado no Google Tag Manager. Permite administrar outras tags sem alterar o código do site.</small></div>
            <div class="col-md-6"><label class="sax-form-label">Google Analytics 4</label><input class="form-control sax-input font-monospace" name="google_analytics_id" value="{{ old('google_analytics_id', $settings->google_analytics_id) }}" placeholder="G-XXXXXXXXXX"><small class="form-text text-muted">ID de medição do fluxo da Web no GA4. Registra visualizações e comportamento básico das páginas.</small></div>
            <div class="col-md-6"><label class="sax-form-label">Google Ads</label><input class="form-control sax-input font-monospace" name="google_ads_id" value="{{ old('google_ads_id', $settings->google_ads_id) }}" placeholder="AW-123456789"><small class="form-text text-muted">ID global da conta de anúncios. Encontre-o na configuração da tag do Google Ads.</small></div>
            <div class="col-md-6"><label class="sax-form-label">Label de conversão Google Ads</label><input class="form-control sax-input font-monospace" name="google_ads_conversion_label" value="{{ old('google_ads_conversion_label', $settings->google_ads_conversion_label) }}"><small class="form-text text-muted">Identificador da ação de conversão, normalmente exibido após <code>AW-XXXXXXXXX/</code>. Não é o ID da conta.</small></div>
            <div class="col-md-6"><label class="sax-form-label">Meta/Facebook Pixel</label><input class="form-control sax-input font-monospace" name="meta_pixel_id" value="{{ old('meta_pixel_id', $settings->meta_pixel_id) }}" placeholder="Somente números"><small class="form-text text-muted">ID numérico do conjunto de dados/pixel encontrado no Gerenciador de Eventos da Meta.</small></div>
            <div class="col-md-6"><label class="sax-form-label">TikTok Pixel</label><input class="form-control sax-input font-monospace" name="tiktok_pixel_id" value="{{ old('tiktok_pixel_id', $settings->tiktok_pixel_id) }}"><small class="form-text text-muted">Identificador do pixel criado no TikTok Ads Manager. Registra visualizações de página.</small></div>
            <div class="col-md-6"><label class="sax-form-label">Pinterest Tag</label><input class="form-control sax-input font-monospace" name="pinterest_tag_id" value="{{ old('pinterest_tag_id', $settings->pinterest_tag_id) }}"><small class="form-text text-muted">ID numérico da tag localizada no Pinterest Ads. Deixe vazio se não houver conta publicitária.</small></div>
            <div class="col-md-6"><label class="sax-form-label">LinkedIn Partner ID</label><input class="form-control sax-input font-monospace" name="linkedin_partner_id" value="{{ old('linkedin_partner_id', $settings->linkedin_partner_id) }}"><small class="form-text text-muted">ID numérico da Insight Tag disponível no Campaign Manager do LinkedIn.</small></div>
            <div class="col-md-6"><label class="sax-form-label">Microsoft Clarity</label><input class="form-control sax-input font-monospace" name="microsoft_clarity_id" value="{{ old('microsoft_clarity_id', $settings->microsoft_clarity_id) }}"><small class="form-text text-muted">ID do projeto no Clarity. Ativa mapas de calor e gravações de navegação.</small></div>
        </div>

        <h2 class="h6 text-uppercase fw-bold border-bottom pb-2 mb-3">Dados estruturados da organização</h2>
        <div class="row g-3 mb-5">
            <div class="col-md-6"><label class="sax-form-label">Nome da organização</label><input class="form-control sax-input" name="organization_name" value="{{ old('organization_name', $settings->organization_name) }}"><small class="form-text text-muted">Razão pública ou nome comercial usado nos dados estruturados do Google.</small></div>
            <div class="col-md-6"><label class="sax-form-label">URL da organização</label><input type="url" class="form-control sax-input" name="organization_url" value="{{ old('organization_url', $settings->organization_url) }}"><small class="form-text text-muted">Endereço oficial completo da empresa, normalmente a página inicial do site.</small></div>
            <div class="col-md-6"><label class="sax-form-label">URL do logotipo</label><input type="url" class="form-control sax-input" name="organization_logo_url" value="{{ old('organization_logo_url', $settings->organization_logo_url) }}"><small class="form-text text-muted">URL pública completa de um logotipo estável, nítido e acessível sem autenticação.</small></div>
            <div class="col-12"><label class="sax-form-label">Redes sociais</label><textarea class="form-control sax-input" name="organization_social_urls" rows="3" placeholder="Uma URL por linha">{{ old('organization_social_urls', $settings->organization_social_urls) }}</textarea><small class="form-text text-muted">Informe uma URL completa por linha, como Instagram, Facebook, LinkedIn, YouTube, Pinterest e TikTok oficiais.</small></div>
        </div>

        <h2 class="h6 text-uppercase fw-bold border-bottom pb-2 mb-3">Códigos personalizados</h2>
        <div class="row g-3">
            <div class="col-12"><label class="sax-form-label">Final do &lt;head&gt;</label><textarea class="form-control sax-input font-monospace" name="custom_head_scripts" rows="7">{{ old('custom_head_scripts', $settings->custom_head_scripts) }}</textarea><small class="form-text text-muted">Para tags que o fornecedor exige dentro do <code>&lt;head&gt;</code>, como verificações e bibliotecas. Cole o bloco completo, incluindo <code>&lt;script&gt;</code> ou <code>&lt;meta&gt;</code>.</small></div>
            <div class="col-12"><label class="sax-form-label">Início do &lt;body&gt;</label><textarea class="form-control sax-input font-monospace" name="custom_body_start_scripts" rows="7">{{ old('custom_body_start_scripts', $settings->custom_body_start_scripts) }}</textarea><small class="form-text text-muted">Para códigos que precisam ficar imediatamente após a abertura do <code>&lt;body&gt;</code>, geralmente blocos <code>&lt;noscript&gt;</code>.</small></div>
            <div class="col-12"><label class="sax-form-label">Final do &lt;body&gt;</label><textarea class="form-control sax-input font-monospace" name="custom_body_end_scripts" rows="7">{{ old('custom_body_end_scripts', $settings->custom_body_end_scripts) }}</textarea><small class="form-text text-muted">Para widgets, chats e scripts que devem carregar depois do conteúdo. É a posição preferível quando o fornecedor não exigir outra.</small></div>
        </div>

        <div class="border-top mt-5 pt-4 text-end"><button class="btn btn-dark px-5 text-uppercase fw-bold" type="submit">Guardar configurações</button></div>
    </form>
</x-admin.card>
@endsection
