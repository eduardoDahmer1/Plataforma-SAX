<?php

namespace App\Http\Controllers;

use App\Models\Bridal;
use App\Models\Attribute;
use Illuminate\Http\Request;

class BridalController extends Controller
{
    public function index()
    {
        $attributes = Attribute::first() ?? new Attribute();

        $bridal = Bridal::first() ?? new Bridal();

        return view('bridal.index', compact('bridal', 'attributes'));
    }
}