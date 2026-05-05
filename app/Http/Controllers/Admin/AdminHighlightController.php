<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Generalsetting;

class AdminHighlightController extends Controller
{
    // 🔹 Adicionado 'famosos' para controlar a nova seção de Mais Vistos
    protected $sections = [
        'destaque',
        'lancamentos',
        'famosos', 
    ];

    public function index()
    {
        $settings = Generalsetting::first();
        // A view agora receberá as três seções para exibição
        return view('admin.sections_home.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $settings = Generalsetting::first();

        // 🔹 O loop agora atualizará destaque, lancamentos e famosos
        foreach ($this->sections as $section) {
            // O Laravel verifica se o checkbox foi marcado no request
            $settings->{'show_highlight_'.$section} = $request->has($section) ? 1 : 0;
        }

        $settings->save();

        return redirect()->back()->with('success', 'Configurações de destaques atualizadas!');
    }
}