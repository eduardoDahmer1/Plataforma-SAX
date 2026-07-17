<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbandonedCart;
use Illuminate\Http\Request;

class AbandonedCartControllerAdmin extends Controller
{
    public function index(Request $request)
    {
        $query = AbandonedCart::with('user')->latest('abandoned_at');
        if ($request->filled('search')) {
            $query->whereHas('user', fn ($q) => $q->where('name', 'like', '%'.$request->search.'%')->orWhere('email', 'like', '%'.$request->search.'%'));
        }
        if ($request->filled('status')) $query->where('status', $request->status);
        return view('admin.abandoned-carts.index', ['carts' => $query->paginate(20)->withQueryString()]);
    }

    public function show(AbandonedCart $abandonedCart)
    {
        $abandonedCart->load('user', 'items.product');
        return view('admin.abandoned-carts.show', compact('abandonedCart'));
    }
}
