@php
use App\Models\Currency;

// Todas as moedas
$currencies = Currency::all();

// Pega a moeda da sessão
$sessionCurrency = session('currency');

// Se a sessão tiver um objeto, pega o ID; senão assume int
if (is_object($sessionCurrency)) {
    $currentCurrency = $sessionCurrency->id ?? Currency::where('is_default',1)->first()?->id;
} else {
    $currentCurrency = $sessionCurrency ?? Currency::where('is_default',1)->first()?->id;
}

$currentCurrency = (int) $currentCurrency;
@endphp

<div class="text-white py-2 px-3 d-flex justify-content-end align-items-center">
    <form action="{{ route('currency.change') }}" method="POST" id="currency-form" class="d-flex align-items-center">
        @csrf
        <select name="currency_id"
                class="form-select form-select-sm border-0 rounded-pill px-3"
                style="width: auto; min-width: 120px; cursor: pointer;"
                onchange="document.getElementById('currency-form').submit()">
            @foreach($currencies as $currency)
                <option value="{{ $currency->id }}"
                    {{ $currency->id === $currentCurrency ? 'selected' : '' }}>
                    {{ $currency->sign }} - {{ $currency->name }}
                </option>
            @endforeach
        </select>
    </form>
</div>
