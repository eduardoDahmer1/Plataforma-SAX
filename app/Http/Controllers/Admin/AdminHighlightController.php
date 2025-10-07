<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Generalsetting;

class AdminHighlightController extends Controller
{
    protected $sections = [
        'destaque',
        'mais_vendidos',
        'melhores_avaliacoes',
        'super_desconto',
        'famosos',
        'lancamentos',
        'tendencias',
        'promocoes',
        'ofertas_relampago',
        'navbar',
    ];

    public function index()
    {
        $settings = Generalsetting::first();
        return view('admin.sections_home.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $settings = Generalsetting::first();

        foreach ($this->sections as $section) {
            $settings->{'show_highlight_'.$section} = $request->has($section) ? 1 : 0;
        }

        $settings->save();

        return redirect()->back()->with('success', 'Seções atualizadas com sucesso!');
    }
}
