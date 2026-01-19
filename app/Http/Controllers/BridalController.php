<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BridalController extends Controller
{
    public function index()
    {
        // Aqui você pode buscar banners ou coleções específicas de noivas se desejar
        return view('bridal.index');
    }
}