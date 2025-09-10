<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class UserPreferenceController extends Controller
{
    // Lista de produtos favoritos do usuário
    public function index()
    {
        $user = Auth::user();
        $favoriteProducts = $user->favoriteProducts()->paginate(12); // Paginação 12 por página

        return view('users.preferences.index', compact('favoriteProducts'));
    }

    // Adicionar ou remover favorito
    public function toggle(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $user = Auth::user();
        $productId = $request->input('product_id');

        if ($user->favoriteProducts()->where('product_id', $productId)->exists()) {
            $user->favoriteProducts()->detach($productId); // remove
            $message = 'Produto removido dos favoritos';
        } else {
            $user->favoriteProducts()->attach($productId); // adiciona
            $message = 'Produto adicionado aos favoritos';
        }

        return back()->with('success', $message);
    }
}
