<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

class UserController extends Controller
{
    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'user_type' => 'required|in:1,2,3',
        ]);

        User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'user_type' => $request->user_type,
        ]);

        // limpa cache da lista de usuários após criar
        Cache::forget('users_all');

        return redirect()->route('admin.clients.index', compact('users', 'userCount'))->with('success', 'Usuário criado com sucesso!');
    }

    public function updateType(Request $request, $id)
    {
        $request->validate([
            'user_type' => 'required|in:1,2,3',
        ]);

        $user = User::findOrFail($id);
        $user->user_type = $request->user_type;
        $user->save();

        // limpa cache da lista de usuários após update
        Cache::forget('users_all');

        return redirect()->back()->with('success', 'Tipo de usuário atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        // limpa cache da lista de usuários após excluir
        Cache::forget('users_all');

        return redirect()->back()->with('success', 'Usuário excluído com sucesso.');
    }

    public function index()
    {
        // mantém cache por 10 minutos
        $users = Cache::remember('users_all', 600, function () {
            return User::orderBy('id', 'desc')->get();
        });

        return view('admin.users.index', compact('users'));
    }
}
