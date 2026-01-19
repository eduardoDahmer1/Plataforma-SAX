<?php

namespace App\Http\Controllers;

use App\Models\Palace;
use App\Models\Attribute;
use Illuminate\Http\Request;

class PalaceController extends Controller
{
    public function index()
    {
        // Busca os dados globais (onde está a logo_palace)
        $attributes = Attribute::first();
        
        // Busca todos os registros de conteúdo do Palace
        $conteudos = Palace::all();
        
        // Passa ambos para a view
        return view('palace.index', compact('conteudos', 'attributes'));
    }
}