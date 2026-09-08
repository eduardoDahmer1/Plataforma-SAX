<?php

namespace App\Services;

use App\Models\ThemeSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ThemeSettingsService
{
    public const CACHE_PREFIX = 'theme.settings.v1.';

    private array $memoizedScopes = [];

    public const SCOPES = [
        'storefront' => ['label' => 'Loja e páginas gerais', 'description' => 'Home, catálogo, busca, produtos e contato.', 'icon' => 'fa-store'],
        'blog' => ['label' => 'Blog', 'description' => 'Listagem, categorias e leitura de artigos.', 'icon' => 'fa-newspaper'],
        'checkout' => ['label' => 'Checkout e área do cliente', 'description' => 'Carrinho, pagamento, conta e pedidos.', 'icon' => 'fa-credit-card'],
        'institutional' => ['label' => 'Institucional SAX', 'description' => 'Identidade independente da página institucional.', 'icon' => 'fa-landmark'],
        'bridal' => ['label' => 'SAX Bridal', 'description' => 'Paleta e tipografia próprias da experiência Bridal.', 'icon' => 'fa-ring'],
        'palace' => ['label' => 'SAX Palace', 'description' => 'Paleta vinho e linguagem editorial do Palace.', 'icon' => 'fa-crown'],
        'bistro_pjc' => ['label' => 'Bistrô PJC', 'description' => 'Tema exclusivo de Pedro Juan Caballero.', 'icon' => 'fa-mug-hot'],
        'bistro_asuncion' => ['label' => 'Bistrô Assunção', 'description' => 'Tema exclusivo da unidade Assunção.', 'icon' => 'fa-location-dot'],
    ];

    public const FONT_OPTIONS = [
        'original' => ['label' => 'Original da página (recomendado)', 'stack' => null, 'google' => null],
        'roboto' => ['label' => 'Roboto', 'stack' => "'Roboto', Arial, sans-serif", 'google' => 'Roboto:wght@300;400;500;600;700;800'],
        'helvetica' => ['label' => 'Helvetica / Arial', 'stack' => "'Helvetica Neue', Helvetica, Arial, sans-serif", 'google' => null],
        'montserrat' => ['label' => 'Montserrat', 'stack' => "'Montserrat', Arial, sans-serif", 'google' => 'Montserrat:wght@300;400;500;600;700;800'],
        'playfair' => ['label' => 'Playfair Display', 'stack' => "'Playfair Display', Georgia, serif", 'google' => 'Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400'],
        'cormorant' => ['label' => 'Cormorant Garamond', 'stack' => "'Cormorant Garamond', Georgia, serif", 'google' => 'Cormorant+Garamond:wght@400;500;600;700'],
        'lora' => ['label' => 'Lora', 'stack' => "'Lora', Georgia, serif", 'google' => 'Lora:wght@400;500;600;700'],
        'inter' => ['label' => 'Inter', 'stack' => "'Inter', Arial, sans-serif", 'google' => 'Inter:wght@300;400;500;600;700;800'],
        'georgia' => ['label' => 'Georgia', 'stack' => 'Georgia, serif', 'google' => null],
        'system' => ['label' => 'Sistema', 'stack' => 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif', 'google' => null],
    ];

    public function defaults(string $scope): array
    {
        $base = [
            'primary_color' => '#111111',
            'accent_color' => '#c5a059',
            'background_color' => '#ffffff',
            'surface_color' => '#f7f7f7',
            'heading_color' => '#1a1a1a',
            'subtitle_color' => '#666666',
            'body_color' => '#333333',
            'inverse_text_color' => '#ffffff',
            'link_color' => '#1a1a1a',
            'button_background' => '#111111',
            'button_text' => '#ffffff',
            'header_background_color' => '#ffffff',
            'header_text_color' => '#000000',
            'header_accent_color' => '#c8a64b',
            'footer_background_color' => '#ffffff',
            'footer_heading_color' => '#1a1a1a',
            'footer_text_color' => '#666666',
            'border_color' => '#d8d8d8',
            'hover_color' => '#c8a64b',
            'heading_font' => 'original',
            'body_font' => 'original',
            'header_font' => 'original',
            'footer_font' => 'original',
            'heading_weight' => 'original',
            'base_font_size' => 'original',
            'button_radius' => 'original',
            'card_radius' => 'original',
        ];

        return array_replace($base, match ($scope) {
            'blog' => ['accent_color' => '#b2945e', 'surface_color' => '#f7f7f7'],
            'checkout' => ['accent_color' => '#b2945e', 'surface_color' => '#f6f6f6'],
            'institutional' => ['primary_color' => '#111111', 'accent_color' => '#c5a059', 'surface_color' => '#fafafa', 'header_background_color' => '#111111', 'header_text_color' => '#ffffff', 'header_accent_color' => '#c5a059', 'footer_heading_color' => '#1a1a1a', 'footer_text_color' => '#666666', 'border_color' => '#eeeeee', 'hover_color' => '#c5a059'],
            'bridal' => ['primary_color' => '#2c2c2c', 'accent_color' => '#c9a961', 'background_color' => '#faf8f5', 'surface_color' => '#ffffff', 'subtitle_color' => '#888888', 'header_background_color' => '#faf8f5', 'header_text_color' => '#2c2c2c', 'header_accent_color' => '#c9a961', 'footer_heading_color' => '#2c2c2c', 'footer_text_color' => '#888888', 'border_color' => '#faf8f5', 'hover_color' => '#c9a961'],
            'palace' => ['primary_color' => '#681711', 'accent_color' => '#dab167', 'background_color' => '#280705', 'surface_color' => '#f7f0e5', 'heading_color' => '#35100d', 'subtitle_color' => '#8b6961', 'body_color' => '#5c3933', 'inverse_text_color' => '#fffaf0', 'link_color' => '#dab167', 'button_background' => '#dab167', 'button_text' => '#280705', 'header_background_color' => '#300806', 'header_text_color' => '#fffaf0', 'header_accent_color' => '#dab167', 'footer_background_color' => '#280705', 'footer_heading_color' => '#fffaf0', 'footer_text_color' => '#d6c6b6', 'border_color' => '#dab167', 'hover_color' => '#dab167'],
            'bistro_pjc' => ['primary_color' => '#172741', 'accent_color' => '#4a6fa5', 'background_color' => '#0f1d35', 'surface_color' => '#172741', 'heading_color' => '#ffffff', 'subtitle_color' => '#aab8cb', 'body_color' => '#c5cfdd', 'inverse_text_color' => '#ffffff', 'link_color' => '#ffffff', 'button_background' => '#172741', 'header_background_color' => '#0f1d35', 'header_text_color' => '#ffffff', 'header_accent_color' => '#4a6fa5', 'footer_background_color' => '#0f1d35', 'footer_heading_color' => '#ffffff', 'footer_text_color' => '#c5cfdd', 'border_color' => '#4a6fa5', 'hover_color' => '#4a6fa5'],
            'bistro_asuncion' => ['primary_color' => '#54152d', 'accent_color' => '#d5ad70', 'background_color' => '#2b0a19', 'surface_color' => '#f8f1e8', 'heading_color' => '#32131f', 'subtitle_color' => '#7a2947', 'body_color' => '#62434e', 'inverse_text_color' => '#fffaf4', 'link_color' => '#d5ad70', 'button_background' => '#d5ad70', 'button_text' => '#2b0a19', 'header_background_color' => '#35091b', 'header_text_color' => '#fffaf4', 'header_accent_color' => '#d5ad70', 'footer_background_color' => '#2b0a19', 'footer_heading_color' => '#f0d5a5', 'footer_text_color' => '#fffaf4', 'border_color' => '#d5ad70', 'hover_color' => '#f0d5a5'],
            default => [],
        });
    }

    public function normalize(string $scope, array $settings): array
    {
        $defaults = $this->defaults($scope);
        $normalized = [];

        foreach ($defaults as $key => $default) {
            $value = (string) ($settings[$key] ?? $default);

            if (str_ends_with($key, '_color') || in_array($key, ['button_background', 'button_text'], true)) {
                $value = preg_match('/^#[0-9a-fA-F]{6}$/', $value) ? strtolower($value) : $default;
            } elseif (in_array($key, ['heading_font', 'body_font', 'header_font', 'footer_font'], true)) {
                $value = array_key_exists($value, self::FONT_OPTIONS) ? $value : $default;
            } elseif ($key === 'heading_weight') {
                $value = in_array($value, ['original', '400', '500', '600', '700', '800', '900'], true) ? $value : $default;
            } elseif ($key === 'base_font_size') {
                $value = $value === 'original' ? $value : (string) min(20, max(13, (int) $value));
            } elseif ($key === 'button_radius') {
                $value = in_array($value, ['original', '0', '4', '8', '12', '20', '999'], true) ? $value : $default;
            } elseif ($key === 'card_radius') {
                $value = in_array($value, ['original', '0', '4', '8', '12', '16', '24'], true) ? $value : $default;
            }

            $normalized[$key] = $value;
        }

        return $normalized;
    }

    public function allForAdmin(): array
    {
        $stored = collect();

        try {
            if (Schema::hasTable('theme_settings')) {
                $stored = ThemeSetting::query()->get()->keyBy('scope');
            }
        } catch (Throwable) {
            // O painel continua disponível com padrões mesmo durante deploy/migração.
        }

        return collect(self::SCOPES)->mapWithKeys(function (array $meta, string $scope) use ($stored) {
            $record = $stored->get($scope);

            return [$scope => [
                'meta' => $meta,
                'settings' => $this->normalize($scope, $record?->settings ?? []),
                'customized' => (bool) ($record?->is_active),
                'updated_at' => $record?->updated_at?->toIso8601String(),
            ]];
        })->all();
    }

    public function active(string $scope): ?array
    {
        if (!array_key_exists($scope, self::SCOPES)) {
            return null;
        }

        if (array_key_exists($scope, $this->memoizedScopes)) {
            return $this->memoizedScopes[$scope];
        }

        try {
            $cached = Cache::rememberForever(self::CACHE_PREFIX.$scope, function () use ($scope) {
                if (!Schema::hasTable('theme_settings')) {
                    return false;
                }

                $record = ThemeSetting::query()->where('scope', $scope)->where('is_active', true)->first();

                return $record ? $this->normalize($scope, $record->settings ?? []) : false;
            });

            return $this->memoizedScopes[$scope] = is_array($cached) ? $cached : null;
        } catch (Throwable) {
            return $this->memoizedScopes[$scope] = null;
        }
    }

    public function clear(string $scope): void
    {
        Cache::forget(self::CACHE_PREFIX.$scope);
        unset($this->memoizedScopes[$scope]);
    }

    public function scopeForRequest(Request $request): string
    {
        if ($request->routeIs('palace.*')) return 'palace';
        if ($request->routeIs('bridal.*')) return 'bridal';
        if ($request->routeIs('institucional.*')) return 'institutional';
        if ($request->routeIs('cafe_bistro.*')) return $request->route('location') === 'asuncion' ? 'bistro_asuncion' : 'bistro_pjc';
        if ($request->is('blog*', 'blogs*')) return 'blog';
        if ($request->routeIs('checkout.*', 'cart.*', 'user.*', 'dashboard', 'receipts.*')) return 'checkout';

        return 'storefront';
    }

    public function settingsForRequest(Request $request): ?array
    {
        return $this->active($this->scopeForRequest($request));
    }

    public function fontStylesheetUrl(Request $request): ?string
    {
        $settings = $this->settingsForRequest($request);
        if (!$settings) return null;

        $alreadyLoaded = $request->is('*cafe*', '*bistro*', '*bridal*', '*palace*', '*institucional*', '*blog*', '*blogs*')
            ? ['montserrat', 'playfair']
            : [];

        $families = collect([$settings['heading_font'], $settings['body_font'], $settings['header_font'], $settings['footer_font']])
            ->unique()
            ->reject(fn (string $key) => $key === 'original')
            ->reject(fn (string $key) => in_array($key, $alreadyLoaded, true))
            ->map(fn (string $key) => self::FONT_OPTIONS[$key]['google'] ?? null)
            ->filter()
            ->map(fn (string $family) => 'family='.$family)
            ->implode('&');

        return $families ? 'https://fonts.googleapis.com/css2?'.$families.'&display=swap' : null;
    }

    public function cssForRequest(Request $request): string
    {
        $scope = $this->scopeForRequest($request);
        $s = $this->active($scope);
        if (!$s) return '';

        $changed = array_diff_assoc($s, $this->defaults($scope));
        if ($changed === []) return '';

        $fontStack = fn (string $key) => self::FONT_OPTIONS[$s[$key]]['stack'] ?? null;
        $varsBySetting = [
            'primary_color' => ['--sax-theme-primary'], 'accent_color' => ['--sax-theme-accent'],
            'background_color' => ['--sax-theme-background'], 'surface_color' => ['--sax-theme-surface'],
            'heading_color' => ['--sax-theme-heading'], 'subtitle_color' => ['--sax-theme-subtitle'],
            'body_color' => ['--sax-theme-body'], 'inverse_text_color' => ['--sax-theme-inverse'],
            'link_color' => ['--sax-theme-link'], 'button_background' => ['--sax-theme-button-bg'],
            'button_text' => ['--sax-theme-button-text'], 'header_background_color' => ['--sax-theme-header-bg'],
            'header_text_color' => ['--sax-theme-header-text'], 'header_accent_color' => ['--sax-theme-header-accent'],
            'footer_background_color' => ['--sax-theme-footer-bg'], 'footer_heading_color' => ['--sax-theme-footer-heading'],
            'footer_text_color' => ['--sax-theme-footer-text'], 'border_color' => ['--sax-theme-border'],
            'hover_color' => ['--sax-theme-hover'],
        ];
        $vars = [];
        foreach ($varsBySetting as $setting => $names) {
            if (!array_key_exists($setting, $changed)) continue;
            foreach ($names as $name) $vars[$name] = $s[$setting];
        }
        if (isset($changed['button_background']) || isset($changed['button_text'])) {
            $vars['--sax-theme-button-bg'] = $s['button_background'];
            $vars['--sax-theme-button-text'] = $s['button_text'];
        }
        if ($scope === 'storefront' && (isset($changed['background_color']) || isset($changed['body_color']))) {
            $vars['--sax-theme-background'] = $s['background_color'];
            $vars['--sax-theme-body'] = $s['body_color'];
        }
        foreach (['heading_font', 'body_font', 'header_font', 'footer_font'] as $fontKey) {
            if (!isset($changed[$fontKey]) || !$fontStack($fontKey)) continue;
            $vars['--sax-theme-'.str_replace('_', '-', $fontKey)] = $fontStack($fontKey);
        }
        if (isset($changed['heading_weight']) && $s['heading_weight'] !== 'original') $vars['--sax-theme-heading-weight'] = $s['heading_weight'];
        if (isset($changed['base_font_size']) && $s['base_font_size'] !== 'original') $vars['--sax-theme-base-size'] = $s['base_font_size'].'px';
        if (isset($changed['button_radius']) && $s['button_radius'] !== 'original') $vars['--sax-theme-button-radius'] = $s['button_radius'] === '999' ? '999px' : $s['button_radius'].'px';
        if (isset($changed['card_radius']) && $s['card_radius'] !== 'original') $vars['--sax-theme-card-radius'] = $s['card_radius'].'px';

        $nativeMap = match ($scope) {
            'blog' => ['heading_color' => ['--ink'], 'body_color' => ['--txt'], 'subtitle_color' => ['--muted'], 'background_color' => ['--paper'], 'surface_color' => ['--surface'], 'accent_color' => ['--accent'], 'primary_color' => ['--accent-2'], 'border_color' => ['--line']],
            'checkout' => ['heading_color' => ['--ink'], 'body_color' => ['--txt'], 'subtitle_color' => ['--muted'], 'background_color' => ['--paper'], 'surface_color' => ['--surface', '--brand-soft'], 'border_color' => ['--line']],
            'institutional' => ['accent_color' => ['--gold'], 'primary_color' => ['--gold2', '--dark'], 'heading_color' => ['--ink'], 'body_color' => ['--txt'], 'subtitle_color' => ['--muted'], 'background_color' => ['--paper'], 'surface_color' => ['--surface'], 'border_color' => ['--line'], 'header_background_color' => ['--header-scroll-bg'], 'header_text_color' => ['--header-scroll-text'], 'header_accent_color' => ['--header-accent']],
            'bridal' => ['surface_color' => ['--paper'], 'background_color' => ['--cream'], 'accent_color' => ['--gold'], 'primary_color' => ['--gold-light'], 'heading_color' => ['--ink'], 'subtitle_color' => ['--muted'], 'header_background_color' => ['--header-scroll-bg'], 'header_text_color' => ['--header-scroll-text'], 'header_accent_color' => ['--header-accent']],
            'palace' => ['primary_color' => ['--palace-wine'], 'accent_color' => ['--gold'], 'background_color' => ['--palace-wine-dark'], 'surface_color' => ['--palace-cream', '--palace-blush'], 'heading_color' => ['--palace-ink'], 'inverse_text_color' => ['--txt'], 'subtitle_color' => ['--muted'], 'header_background_color' => ['--header-scroll-bg'], 'header_text_color' => ['--header-scroll-text'], 'header_accent_color' => ['--header-accent']],
            'bistro_pjc' => ['background_color' => ['--deep', '--azul-profundo'], 'primary_color' => ['--navy', '--azul-navy'], 'accent_color' => ['--mid', '--light'], 'inverse_text_color' => ['--paper'], 'body_color' => ['--soft'], 'header_background_color' => ['--header-scroll-bg'], 'header_text_color' => ['--header-scroll-text'], 'header_accent_color' => ['--header-accent']],
            'bistro_asuncion' => ['primary_color' => ['--asu-wine'], 'background_color' => ['--asu-wine-dark'], 'subtitle_color' => ['--asu-wine-soft'], 'accent_color' => ['--asu-gold'], 'link_color' => ['--asu-gold-light'], 'surface_color' => ['--asu-cream'], 'heading_color' => ['--asu-ink'], 'inverse_text_color' => ['--paper'], 'header_background_color' => ['--header-scroll-bg'], 'header_text_color' => ['--header-scroll-text'], 'header_accent_color' => ['--header-accent']],
            default => ['primary_color' => ['--blk'], 'heading_color' => ['--ink'], 'subtitle_color' => ['--muted'], 'body_color' => ['--soft'], 'background_color' => ['--paper'], 'surface_color' => ['--surface'], 'link_color' => ['--ok-tx'], 'border_color' => ['--line']],
        };
        foreach ($nativeMap as $setting => $names) {
            if (!array_key_exists($setting, $changed)) continue;
            foreach ($names as $name) $vars[$name] = $s[$setting];
        }
        if (isset($changed['heading_font']) && $fontStack('heading_font') && in_array($scope, ['bridal', 'bistro_pjc', 'bistro_asuncion'], true)) {
            $vars['--serif'] = $fontStack('heading_font');
            if ($scope === 'bridal') $vars['--display'] = $fontStack('heading_font');
        }
        if (isset($changed['body_font']) && $fontStack('body_font') && in_array($scope, ['bridal', 'bistro_pjc', 'bistro_asuncion'], true)) $vars['--sans'] = $fontStack('body_font');

        $declarations = collect($vars)->map(fn ($value, $key) => $key.':'.$value)->implode(';');
        $variableSelector = match ($scope) {
            'palace' => ':root,body.palace-page.experience-page--palace',
            'bridal' => ':root,body.experience-page--bridal',
            'institutional' => ':root,body.experience-page--institutional',
            'bistro_asuncion' => ':root,body.experience-page--bistro.experience-page--bistro-asuncion',
            'bistro_pjc' => ':root,body.experience-page--bistro:not(.experience-page--bistro-asuncion)',
            default => ':root,body.sax-storefront',
        };
        $css = $declarations ? $variableSelector.'{'.$declarations.'}' : '';
        if (isset($changed['body_font'])) $css .= 'body{font-family:var(--sax-theme-body-font)}';
        if (isset($changed['base_font_size'])) $css .= 'body{font-size:var(--sax-theme-base-size)}';
        if (isset($changed['heading_font'])) $css .= 'body :is(h1,h2,h3,h4,h5,h6,.section-title,.section-title-elegant,.palace-section__title,.arabe-title){font-family:var(--sax-theme-heading-font)}';
        if (isset($changed['heading_weight'])) $css .= 'body :is(h1,h2,h3,h4,h5,h6,.section-title,.section-title-elegant,.palace-section__title,.arabe-title){font-weight:var(--sax-theme-heading-weight)}';
        if (isset($changed['heading_color'])) $css .= 'body main :is(h1,h2,h3,h4,h5,h6,.section-title,.section-title-elegant,.palace-section__title,.arabe-title){color:var(--sax-theme-heading)!important}';
        if (isset($changed['subtitle_color'])) $css .= 'body main :is(.subtitle,.section-subtitle,.section-kicker,.eyebrow,.palace-eyebrow,.arabe-subtitle,.text-muted){color:var(--sax-theme-subtitle)!important}';
        if (isset($changed['body_color'])) $css .= 'body main :is(p,.description,.section-description,.card-text,.lead){color:var(--sax-theme-body)!important}';
        if (isset($changed['inverse_text_color'])) $css .= 'body main :is(.hero,.hero-cafe,.palace-hero,.hero-slider,.hero-bridal) :is(h1,h2,h3,p,span){color:var(--sax-theme-inverse)!important}';
        if (isset($changed['link_color'])) $css .= 'body main a:not(.btn):not(.nav-link){color:var(--sax-theme-link)}';
        if (isset($changed['border_color'])) $css .= 'body main :is(.card,.product-card,input,select,textarea,.border,.border-top,.border-bottom){border-color:var(--sax-theme-border)!important}';
        if (isset($changed['header_font'])) $css .= 'body :is(.sax-header,.exp-header,.exp-mobile-appbar),body :is(.sax-header,.exp-header,.exp-mobile-appbar) :is(a,button,p,span,strong,small,li){font-family:var(--sax-theme-header-font)!important}';
        if (isset($changed['footer_font'])) $css .= 'body :is(.sax-footer-refined,.footer-inst,.footer-bridal-v2,.palace-footer,.footer-cafe),body :is(.sax-footer-refined,.footer-inst,.footer-bridal-v2,.palace-footer,.footer-cafe) :is(a,button,p,span,strong,small,h1,h2,h3,h4,h5,h6,li,td){font-family:var(--sax-theme-footer-font)!important}';
        if (isset($changed['header_background_color'])) $css .= 'body .sax-header,body .sax-header .sax-header-main,body .exp-header.scrolled,body .exp-mobile-appbar{background-color:var(--sax-theme-header-bg)!important}';
        if (isset($changed['header_text_color'])) $css .= 'body :is(.sax-header,.exp-header,.exp-mobile-appbar) :is(a,button,.nav-link,.dropdown-toggle){color:var(--sax-theme-header-text)!important}';
        if (isset($changed['header_accent_color'])) $css .= 'body :is(.sax-header,.exp-header,.exp-mobile-appbar) :is(a,button,.nav-link):hover{color:var(--sax-theme-header-accent)!important}';
        if (isset($changed['footer_background_color'])) $css .= 'body :is(.sax-footer-refined,.footer-inst,.footer-bridal-v2,.palace-footer,.footer-cafe){background-color:var(--sax-theme-footer-bg)!important}';
        if (isset($changed['footer_heading_color'])) $css .= 'body :is(.sax-footer-refined,.footer-inst,.footer-bridal-v2,.palace-footer,.footer-cafe) :is(h2,h3,h4,h5,.footer-title,.footer-col-title,.footer-brand-inst,.footer-brand-v2){color:var(--sax-theme-footer-heading)!important}';
        if (isset($changed['footer_text_color'])) $css .= 'body :is(.sax-footer-refined,.footer-inst,.footer-bridal-v2,.palace-footer,.footer-cafe) :is(p,li,a,span,td,.footer-desc-inst,.footer-desc-v2,.footer-copyright,.palace-footer__copy){color:var(--sax-theme-footer-text)!important}';
        if (isset($changed['hover_color'])) $css .= 'body :is(main,footer) a:hover{color:var(--sax-theme-hover)!important}';
        if (isset($changed['button_radius'])) $css .= 'body :is(.btn,button[class*="btn"],a[class*="btn"]){border-radius:var(--sax-theme-button-radius)!important}';
        if (isset($changed['card_radius'])) $css .= 'body :is(.card,.product-card,.blog-card,.palace-card,.card-feature){border-radius:var(--sax-theme-card-radius)!important}';

        $css .= match ($scope) {
            'palace' => (isset($changed['background_color']) ? 'body.palace-page,body.palace-page .palace-main{background-color:var(--sax-theme-background)!important}' : '').(isset($changed['button_background']) || isset($changed['button_text']) ? 'body.palace-page :is(.palace-btn--gold,.btn-arabe-gold){background:var(--sax-theme-button-bg)!important;color:var(--sax-theme-button-text)!important}' : ''),
            'bridal' => (isset($changed['background_color']) ? 'body.experience-page--bridal{background-color:var(--sax-theme-background)!important}' : '').(isset($changed['button_background']) || isset($changed['button_text']) ? 'body.experience-page--bridal :is(.btn-sax,.btn-primary){background:var(--sax-theme-button-bg)!important;color:var(--sax-theme-button-text)!important}' : ''),
            'bistro_pjc', 'bistro_asuncion' => (isset($changed['background_color']) ? 'body.experience-page--bistro,body.experience-page--bistro main{background-color:var(--sax-theme-background)!important}' : '').(isset($changed['button_background']) || isset($changed['button_text']) ? 'body.experience-page--bistro :is(.btn-cafe-primary,.btn-cafe-white){background:var(--sax-theme-button-bg)!important;color:var(--sax-theme-button-text)!important}' : ''),
            'institutional' => (isset($changed['background_color']) ? 'body.experience-page--institutional{background-color:var(--sax-theme-background)!important}' : '').(isset($changed['button_background']) || isset($changed['button_text']) ? 'body.experience-page--institutional :is(.btn-gold,.btn-sax,.btn-primary){background:var(--sax-theme-button-bg)!important;color:var(--sax-theme-button-text)!important}' : ''),
            default => (isset($changed['background_color']) || isset($changed['body_color']) ? 'body{background-color:var(--sax-theme-background);color:var(--sax-theme-body)}' : '').(isset($changed['link_color']) ? 'body main a:not(.btn):not(.nav-link){color:var(--sax-theme-link)}' : '').(isset($changed['button_background']) || isset($changed['button_text']) ? 'body :is(.btn-primary,.btn-dark){background:var(--sax-theme-button-bg)!important;border-color:var(--sax-theme-button-bg)!important;color:var(--sax-theme-button-text)!important}' : ''),
        };

        return $css;
    }
}
