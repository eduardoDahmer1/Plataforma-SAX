<?php

namespace App\Http\Controllers;

use App\Models\Institucional;
use App\Models\Brand;
use Illuminate\Http\Request;

class InstitucionalController extends Controller
{
    public function index()
    {
        $institucional = Institucional::first() ?: new Institucional();

        $brands = Brand::whereNotNull('image')
            ->where('status', 1)
            ->get();

        return view('institucional.index', compact('institucional', 'brands'));
    }
}