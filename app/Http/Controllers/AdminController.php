<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Exibe a página principal do administrador.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin.admin');
    }

}