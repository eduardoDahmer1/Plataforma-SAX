<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Generalsetting;

class AdminHighlightController extends Controller
{
    // 🔹 Reduzido para conter apenas as seções ativas
    protected $sections = [
        'destaque',
        'lancamentos',
    ];

    public function index()
    {
        $settings = Generalsetting::first();
        // Certifique-se de que a view exiba apenas os checkboxes para estas duas seções
        return view('admin.sections_home.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $settings = Generalsetting::first();

        // 🔹 O loop agora atualizará apenas 'show_highlight_destaque' e 'show_highlight_lancamentos'
        foreach ($this->sections as $section) {
            $settings->{'show_highlight_'.$section} = $request->has($section) ? 1 : 0;
        }

        $settings->save();

        return redirect()->back()->with('success', 'Configurações de destaques atualizadas!');
    }
}