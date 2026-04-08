<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Language; // Importe o model de tradução
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

class ContactController extends Controller
{
    public function showForm()
    {
        // 1. Pega o locale da sessão (definido pela CurrencyController) ou usa o padrão
        $locale = session('locale', config('app.locale'));
        
        // 2. Garante que o App use o locale correto nesta requisição
        App::setLocale($locale);

        // 3. Busca todas as traduções para a View (opcional se você usa o helper __())
        $lang = Language::all();

        return view('contact.form', compact('lang', 'locale'));
    }

    public function store(Request $request)
    {
        $type = (int) $request->input('contact_type');

        // Sugestão: Traduzir as mensagens de erro do Validate futuramente
        $rules = [
            'name' => 'required|string|max:255',
            'contact_type' => 'required|in:1,2',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
            'message' => $type === 1 ? 'required|string' : 'nullable|string',
            'attachment' => $type === 2 ? 'required|file|mimes:pdf,jpg,jpeg,png|max:2048' : 'nullable',
        ];

        $validated = $request->validate($rules);

        if ($request->hasFile('attachment')) {
            $validated['attachment'] = $request->file('attachment')
                ->store('attachments', 'public');
        }

        Contact::create($validated);

        // Tradução da mensagem de sucesso
        $successMsg = __('messages.mensagem_sucesso') ?? 'Mensagem enviada com sucesso!';

        return redirect()->back()->with('success', $successMsg);
    }
}