<?php

namespace App\Http\Controllers;

use App\Models\Palace;
use App\Models\Attribute;
use Illuminate\Http\Request;

class PalaceController extends Controller
{
    public function index()
    {
        $attributes = Attribute::first() ?? new Attribute();

        // Alterado de $conteudos para $palace para alinhar com a View
        $palace = Palace::first() ?? new Palace();

        return view('palace.index', compact('palace', 'attributes'));
    }
}