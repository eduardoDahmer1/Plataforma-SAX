<?php

namespace App\Http\Controllers;

use App\Models\Palace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PalaceController extends Controller
{
    public function index()
    {
        // Alterado de $conteudos para $palace para alinhar com a View
        $palace = Cache::remember('palace_data', 28800, fn() => Palace::first()) ?? new Palace();

        return view('palace.index', compact('palace'));
    }
}