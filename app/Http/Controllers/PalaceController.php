<?php

namespace App\Http\Controllers;

use App\Models\Palace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\App;

class PalaceController extends Controller
{
    public function index()
    {
        $palace = Cache::remember('palace_data', 28800, function () {
            return Palace::with('translations')->first() ?? new Palace();
        });

        return view('palace.index', compact('palace'));
    }
}