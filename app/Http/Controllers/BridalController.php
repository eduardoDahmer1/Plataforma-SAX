<?php

namespace App\Http\Controllers;

use App\Models\Bridal;
use App\Models\Brand;
use App\Models\Attribute;
use Illuminate\Http\Request;

class BridalController extends Controller
{
    public function index()
    {
        $attributes = Attribute::first() ?? new Attribute();
        $bridal = Bridal::first() ?? new Bridal();
        $idbrands = [641, 1444, 1237, 1236, 664, 951, 610];

        $brands = Brand::where('status', 1 )->whereIn('id', $idbrands)->get();
               
        
        return view('bridal.index', compact('bridal', 'attributes', 'brands'));
    }
}
