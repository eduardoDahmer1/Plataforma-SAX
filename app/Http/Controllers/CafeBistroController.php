<?php

namespace App\Http\Controllers;

use App\Models\CafeBistro;
use Illuminate\Support\Facades\Cache;

class CafeBistroController extends Controller
{
    public function index()
    {
        $cafeBistro = Cache::remember('cafe_bistro_data', 28800, fn() => CafeBistro::first()) ?? new CafeBistro();

        return view('cafe_bistro.index', compact('cafeBistro'));
    }
}
