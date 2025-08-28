<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Currency;

class CurrencyControllerAdmin extends Controller
{
    // Listar moedas
    public function index()
    {
        $currencies = Currency::all();
        return view('admin.coin.index', compact('currencies'));
    }

    // Adicionar nova moeda
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:currencies,name',
            'description' => 'required|string',
            'sign' => 'required|string|max:5',
            'value' => 'required|numeric|min:0',
            'decimal_separator' => 'nullable|string|max:1',
            'thousands_separator' => 'nullable|string|max:1',
            'decimal_digits' => 'nullable|integer|min:0|max:5',
        ]);

        Currency::create([
            'name' => strtoupper($request->name),
            'description' => $request->description,
            'sign' => $request->sign,
            'value' => $request->value,
            'decimal_separator' => $request->decimal_separator ?? '.',
            'thousands_separator' => $request->thousands_separator ?? ',',
            'decimal_digits' => $request->decimal_digits ?? 2,
            'is_default' => false,
        ]);

        return back()->with('success', 'Moeda adicionada com sucesso!');
    }

    // Atualizar moeda existente
    public function update(Request $request, $id)
    {
        $currency = Currency::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:50|unique:currencies,name,' . $currency->id,
            'description' => 'required|string',
            'sign' => 'required|string|max:5',
            'value' => 'required|numeric|min:0',
            'decimal_separator' => 'nullable|string|max:1',
            'thousands_separator' => 'nullable|string|max:1',
            'decimal_digits' => 'nullable|integer|min:0|max:5',
        ]);

        $currency->update([
            'name' => strtoupper($request->name),
            'description' => $request->description,
            'sign' => $request->sign,
            'value' => $request->value,
            'decimal_separator' => $request->decimal_separator ?? '.',
            'thousands_separator' => $request->thousands_separator ?? ',',
            'decimal_digits' => $request->decimal_digits ?? 2,
        ]);

        return back()->with('success', 'Moeda atualizada com sucesso!');
    }

    // Definir moeda padrão
    public function setDefault($id)
    {
        $currency = Currency::findOrFail($id);

        // Zera todas como padrão
        Currency::query()->update(['is_default' => false]);

        $currency->update(['is_default' => true]);

        return back()->with('success', 'Moeda definida como padrão!');
    }
}
