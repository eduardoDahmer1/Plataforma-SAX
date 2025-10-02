<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserPreferenceController extends Controller
{
    // Lista de produtos favoritos do usuário
    public function index()
    {
        $user = Auth::user();

        // Bloqueio para user_type = 1
        if ($user->user_type == 1) {
            return redirect()->route('home')
                ->with('erro', 'Seu perfil não permite usar favoritos');
        }

        $favoriteProducts = $user->favoriteProducts()->paginate(12);

        return view('users.preferences.index', compact('favoriteProducts'));
    }

    // Adicionar ou remover favorito
    public function toggle(Request $request)
    {
        $user = Auth::user();

        // Bloqueio para user_type = 1
        if ($user->user_type == 1) {
            return back()->with('error', 'Seu perfil não permite favoritar produtos.');
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

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
