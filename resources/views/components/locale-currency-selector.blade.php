@props(['variant' => 'desktop'])

@php
    use App\Models\Currency;

    // --- Idioma (independente da moeda) ---
    $langMap = [
        'pt_BR' => ['flag' => '🇧🇷', 'label' => 'Português'],
        'en'    => ['flag' => '🇺🇸', 'label' => 'English'],
        'es'    => ['flag' => '🇵🇾', 'label' => 'Español'],
    ];

    $currentLocale = app()->getLocale();
    $currentLang   = $langMap[$currentLocale] ?? $langMap['pt_BR'];

    // --- Moeda (independente do idioma) ---
    $currencies = Currency::orderByDesc('is_default')->get();

    $sessionCurrency = session('currency');
    $currentCurrencyId = is_object($sessionCurrency)
        ? ($sessionCurrency->id ?? null)
        : (is_array($sessionCurrency) ? ($sessionCurrency['id'] ?? $sessionCurrency[0] ?? null) : $sessionCurrency);

    $currentCurrency = $currencies->firstWhere('id', (int) $currentCurrencyId)
        ?? $currencies->firstWhere('is_default', 1)
        ?? $currencies->first();

    $currencyLabel = function ($currency) {
        return trim(strtoupper($currency->name) . ' (' . $currency->sign . ')');
    };

    // Taxa exibida apenas quando a moeda escolhida não é a moeda base (USD).
    $rateText = null;
    if ($currentCurrency && !$currentCurrency->is_default) {
        $decimals = ($currentCurrency->value == floor($currentCurrency->value)) ? 0 : 2;
        $rateText = 'U$1.00 = ' . $currentCurrency->sign . ' ' . number_format($currentCurrency->value, $decimals, ',', '.');
    }

    $uid = 'saxLC_' . $variant;
@endphp

@if (in_array($variant, ['nav', 'nav-mobile'], true))
    @php
        // Mantém o visual já existente nos headers das páginas internas.
        $navMenuClass = $variant === 'nav-mobile'
            ? 'dropdown-menu bg-transparent border-0 ps-3'
            : 'dropdown-menu dropdown-menu-end border-0 shadow-lg exp-lang-menu';
    @endphp

    {{-- Variante para os headers das páginas internas (dentro de <ul class="navbar-nav">) --}}
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="{{ $uid }}_lang" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-globe"></i> {{ $currentLang['label'] }}
        </a>
        <ul class="{{ $navMenuClass }}" aria-labelledby="{{ $uid }}_lang">
            @foreach ($langMap as $localeKey => $lang)
                <li>
                    <a href="{{ route('lang.switch', $localeKey) }}"
                       class="dropdown-item {{ $currentLocale === $localeKey ? 'active' : '' }}">
                        {{ $lang['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </li>

    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="{{ $uid }}_curr" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-cash-coin"></i> {{ $currentCurrency ? $currencyLabel($currentCurrency) : __('messages.moeda') }}
        </a>
        <ul class="{{ $navMenuClass }}" aria-labelledby="{{ $uid }}_curr">
            @foreach ($currencies as $currency)
                <li>
                    <form action="{{ route('currency.change') }}" method="POST" class="m-0">
                        @csrf
                        <input type="hidden" name="currency_id" value="{{ $currency->id }}">
                        <button type="submit"
                            class="dropdown-item {{ (int) $currency->id === (int) ($currentCurrency->id ?? 0) ? 'active' : '' }}">
                            {{ $currencyLabel($currency) }}
                        </button>
                    </form>
                </li>
            @endforeach
        </ul>
    </li>
@else
    {{-- Variantes desktop / mobile / checkout --}}
    <div class="sax-lc-wrap sax-lc--{{ $variant }}">

        {{-- Idioma --}}
        <div class="dropdown">
            <button class="sax-lc-trigger dropdown-toggle" type="button"
                id="{{ $uid }}_lang_btn"
                data-bs-toggle="dropdown" aria-expanded="false"
                data-bs-auto-close="outside"
                data-bs-target="#{{ $uid }}_lang"
                {{ $variant === 'mobile' ? 'data-bs-display=static' : '' }}>
                <span class="sax-lc-trigger-top">
                    <span class="sax-twemoji">{{ $currentLang['flag'] }}</span>
                    <span>{{ $currentLang['label'] }}</span>
                </span>
            </button>
            <ul id="{{ $uid }}_lang"
                class="dropdown-menu sax-lc-menu border-0 shadow {{ $variant === 'mobile' ? 'position-static shadow-none' : '' }}"
                aria-labelledby="{{ $uid }}_lang_btn">
                @foreach ($langMap as $localeKey => $lang)
                    <li>
                        <a href="{{ route('lang.switch', $localeKey) }}"
                           class="sax-lc-option {{ $currentLocale === $localeKey ? 'active' : '' }}">
                            <span class="sax-twemoji">{{ $lang['flag'] }}</span>
                            {{ $lang['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <span class="sax-lc-divider" aria-hidden="true"></span>

        {{-- Moeda --}}
        <div class="dropdown">
            <button class="sax-lc-trigger dropdown-toggle" type="button"
                id="{{ $uid }}_curr_btn"
                data-bs-toggle="dropdown" aria-expanded="false"
                data-bs-auto-close="outside"
                data-bs-target="#{{ $uid }}_curr"
                {{ $variant === 'mobile' ? 'data-bs-display=static' : '' }}>
                <span class="sax-lc-trigger-top">
                    <span>{{ $currentCurrency ? $currencyLabel($currentCurrency) : __('messages.moeda') }}</span>
                </span>
                @if ($rateText)
                    <span class="sax-lc-rate-inner">{{ __('messages.taxa_de_cambio') }}: {{ $rateText }}</span>
                @endif
            </button>
            <ul id="{{ $uid }}_curr"
                class="dropdown-menu sax-lc-menu border-0 shadow {{ $variant === 'mobile' ? 'position-static shadow-none' : '' }}"
                aria-labelledby="{{ $uid }}_curr_btn">
                @foreach ($currencies as $currency)
                    <li>
                        <form action="{{ route('currency.change') }}" method="POST" class="m-0">
                            @csrf
                            <input type="hidden" name="currency_id" value="{{ $currency->id }}">
                            <button type="submit"
                                class="sax-lc-option {{ (int) $currency->id === (int) ($currentCurrency->id ?? 0) ? 'active' : '' }}">
                                {{ $currencyLabel($currency) }}
                            </button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </div>

        @if ($rateText)
            <span class="sax-lc-rate">{{ __('messages.taxa_de_cambio') }}: {{ $rateText }}</span>
        @endif
    </div>
@endif

@once
<style>
    .sax-lc-wrap {
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }

    .sax-lc-divider {
        width: 1px;
        height: 0.9rem;
        background: #d9d9d9;
    }

    /* --- Trigger --- */
    .sax-lc-trigger {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        background: transparent;
        border: none;
        padding: 0;
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.5px;
        color: #555;
        cursor: pointer;
        text-transform: uppercase;
        line-height: 1;
    }
    .sax-lc-trigger::after { border-top-color: #999; }
    .sax-lc-trigger:hover,
    .sax-lc-trigger:focus { color: #000; box-shadow: none; }

    .sax-lc-trigger-top {
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }
    .sax-lc-rate-inner { display: none; }

    /* --- Taxa --- */
    .sax-lc-rate {
        font-size: 0.68rem;
        color: #777;
        letter-spacing: 0.2px;
        white-space: nowrap;
    }

    /* --- Menu --- */
    .sax-lc-menu {
        min-width: 10rem;
        border-top: 2px solid #666 !important;
        border-radius: 0;
        padding: 0.4rem 0;
        background: #fff;
    }

    /* --- Opção --- */
    .sax-lc-option {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        width: 100%;
        padding: 0.5rem 1rem;
        background: transparent;
        border: none;
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        color: #555;
        cursor: pointer;
        text-align: left;
        text-decoration: none;
        transition: color 0.2s, padding-left 0.2s;
    }
    .sax-lc-option:hover   { color: #000; padding-left: 1.3rem; }
    .sax-lc-option.active  { color: #666; font-weight: 700; }

    /* --- Twemoji normaliza o tamanho da imagem gerada --- */
    .sax-twemoji img {
        width: 1.1em;
        height: 1.1em;
        vertical-align: -0.1em;
        display: inline-block;
    }

    /* --- Variante mobile (drawer) --- */
    .sax-lc--mobile {
        flex-direction: column;
        align-items: stretch;
        gap: 0;
        width: 100%;
    }
    .sax-lc--mobile .sax-lc-divider { display: none; }
    .sax-lc--mobile .sax-lc-trigger {
        flex-direction: column;
        align-items: flex-start;
        position: relative;
        font-size: 0.84rem;
        font-weight: 700;
        padding: 0.9rem 1.1rem;
        padding-right: 2.5rem;
        color: #1f2937;
        border-bottom: 1px solid #f1f5f9;
        width: 100%;
        letter-spacing: 0.04em;
    }
    .sax-lc--mobile .sax-lc-trigger::after {
        position: absolute;
        right: 1.1rem;
        top: 1.05rem;
        border-top-color: #94a3b8;
    }
    .sax-lc--mobile .sax-lc-rate-inner {
        display: block;
        font-size: 0.66rem;
        color: #64748b;
        font-weight: 400;
        text-transform: none;
        letter-spacing: 0.2px;
        margin-top: 0.25rem;
    }
    .sax-lc--mobile .sax-lc-menu.position-static {
        border-top: none !important;
        background: transparent;
    }
    .sax-lc--mobile .sax-lc-option {
        font-size: 0.8rem;
        color: #334155;
        padding: 0.55rem 1.1rem;
        letter-spacing: 0.04em;
    }
    .sax-lc--mobile .sax-lc-option:hover {
        color: #0f172a;
        background: #f8fafc;
        padding-left: 1.1rem;
    }
    .sax-lc--mobile .sax-lc-rate { display: none; }
</style>

<script src="https://cdn.jsdelivr.net/npm/twemoji@14/dist/twemoji.min.js" crossorigin="anonymous"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.sax-twemoji').forEach(function (el) {
        twemoji.parse(el, { folder: 'svg', ext: '.svg' });
    });

    document.querySelectorAll('.sax-lc-option.active').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
        });
    });
});
</script>
@endonce
