<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = User::all();
        $userCount = $users->count();

        return view('admin.users.index', compact('users', 'userCount'));
    }

    public function updateType(Request $request, User $user)
    {
        $request->validate([
            'user_type' => 'required|in:1,2',
        ]);

        $user->user_type = $request->input('user_type');
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'Tipo de usuário atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Usuário excluído com sucesso.');
    }
}
