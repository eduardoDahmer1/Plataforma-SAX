@props(['variant' => 'desktop'])

@php
use App\Models\Currency;

$currencies        = Currency::all();
$sessionCurrency   = session('currency');
$defaultCurrencyId = Currency::where('name', 'BRL')->value('id')
    ?? Currency::where('is_default', 1)->value('id');
$currentCurrencyId = is_object($sessionCurrency)
    ? $sessionCurrency->id ?? $defaultCurrencyId
    : $sessionCurrency ?? $defaultCurrencyId;

$currentCurrency = $currencies->firstWhere('id', (int) $currentCurrencyId);

$langMap = [
    'BRL' => ['flag' => '🇧🇷', 'label' => 'Português'],
    'USD' => ['flag' => '🇺🇸', 'label' => 'English'],
    'PYG' => ['flag' => '🇵🇾', 'label' => 'Español'],
];

$currentLang = $langMap[strtoupper($currentCurrency->name ?? '')] ?? ['flag' => '🌐', 'label' => 'Idioma'];

$rateText = null;
if ($currentCurrency && !$currentCurrency->is_default) {
    $decimals = ($currentCurrency->value == floor($currentCurrency->value)) ? 0 : 2;
    $rateText = 'U$1.00 = ' . $currentCurrency->sign . ' ' . number_format($currentCurrency->value, $decimals, ',', '.');
}
@endphp

<div class="sax-lang-wrap sax-lang--{{ $variant }}">
    <div class="dropdown">
        <button class="sax-lang-trigger dropdown-toggle" type="button"
            data-bs-toggle="dropdown" aria-expanded="false"
            {{ $variant === 'mobile' ? 'data-bs-display=static' : '' }}>
            <span class="sax-lang-trigger-top">
                <span class="sax-twemoji">{{ $currentLang['flag'] }}</span>
                <span>{{ $currentLang['label'] }}</span>
            </span>
            @if ($rateText)
                <span class="sax-lang-rate-inner">Taxa de câmbio: {{ $rateText }}</span>
            @endif
        </button>
        <ul class="dropdown-menu sax-lang-menu border-0 shadow {{ $variant === 'mobile' ? 'position-static shadow-none' : '' }}">
            @foreach ($currencies as $currency)
                @php $lang = $langMap[strtoupper($currency->name)] ?? null; @endphp
                @if ($lang)
                <li>
                    <form action="{{ route('currency.change') }}" method="POST" class="m-0">
                        @csrf
                        <input type="hidden" name="currency_id" value="{{ $currency->id }}">
                        <button type="submit"
                            class="sax-lang-option {{ (int) $currency->id === (int) $currentCurrencyId ? 'active' : '' }}">
                            <span class="sax-twemoji">{{ $lang['flag'] }}</span>
                            {{ $lang['label'] }}
                        </button>
                    </form>
                </li>
                @endif
            @endforeach
        </ul>
    </div>

    @if ($rateText)
        <span class="sax-lang-rate">Taxa de câmbio: {{ $rateText }}</span>
    @endif
</div>

@once
<style>
    .sax-lang-wrap {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    /* --- Trigger --- */
    .sax-lang-trigger {
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
    .sax-lang-trigger::after {
        border-top-color: #999;
    }
    .sax-lang-trigger:hover,
    .sax-lang-trigger:focus { color: #000; box-shadow: none; }

    .sax-lang-trigger-top {
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }
    .sax-lang-rate-inner { display: none; }

    /* --- Taxa --- */
    .sax-lang-rate {
        font-size: 0.68rem;
        color: #777;
        letter-spacing: 0.2px;
        white-space: nowrap;
    }

    /* --- Menu desktop --- */
    .sax-lang-menu {
        min-width: 10rem;
        border-top: 2px solid #666 !important;
        border-radius: 0;
        padding: 0.4rem 0;
        background: #fff;
    }

    /* --- Opção --- */
    .sax-lang-option {
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
        transition: color 0.2s, padding-left 0.2s;
    }
    .sax-lang-option:hover   { color: #000; padding-left: 1.3rem; }
    .sax-lang-option.active  { color: #666; font-weight: 700; }

    /* --- Twemoji normaliza o tamanho da imagem gerada --- */
    .sax-twemoji img {
        width: 1.1em;
        height: 1.1em;
        vertical-align: -0.1em;
        display: inline-block;
    }

    /* --- Variante mobile (drawer) --- */
    .sax-lang--mobile .sax-lang-trigger {
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
    .sax-lang--mobile .sax-lang-trigger::after {
        position: absolute;
        right: 1.1rem;
        top: 1.05rem;
        border-top-color: #94a3b8;
    }
    .sax-lang--mobile .sax-lang-rate-inner {
        display: block;
        font-size: 0.66rem;
        color: #64748b;
        font-weight: 400;
        text-transform: none;
        letter-spacing: 0.2px;
        margin-top: 0.25rem;
    }
    .sax-lang--mobile .sax-lang-menu.position-static {
        border-top: none !important;
        background: transparent;
    }
    .sax-lang--mobile .sax-lang-option {
        font-size: 0.8rem;
        color: #334155;
        padding: 0.55rem 1.1rem;
        letter-spacing: 0.04em;
    }
    .sax-lang--mobile .sax-lang-option:hover {
        color: #0f172a;
        background: #f8fafc;
        padding-left: 1.1rem;
    }
    .sax-lang--mobile .sax-lang-rate   { display: none; }
</style>

<script src="https://cdn.jsdelivr.net/npm/twemoji@14/dist/twemoji.min.js" crossorigin="anonymous"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.sax-twemoji').forEach(function (el) {
        twemoji.parse(el, { folder: 'svg', ext: '.svg' });
    });
});
</script>
@endonce
