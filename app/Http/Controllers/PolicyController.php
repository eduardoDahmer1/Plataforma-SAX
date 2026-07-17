<?php

namespace App\Http\Controllers;

use App\Models\Policy;

class PolicyController extends Controller
{
    public function index()
    {
        $policies = Policy::where('is_active', true)->orderBy('id')->get();

        return view('policies.index', compact('policies'));
    }
}
