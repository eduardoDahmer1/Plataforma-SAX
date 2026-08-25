<?php

namespace App\Http\Controllers;

use App\Models\Policy;
use Illuminate\Support\Facades\Cache;

class PolicyController extends Controller
{
    public function index()
    {
        $policies = Cache::remember('public_active_policies', now()->addMinutes(30), fn () =>
            Policy::where('is_active', true)->orderBy('id')->get()
        );

        return view('policies.index', compact('policies'));
    }
}
