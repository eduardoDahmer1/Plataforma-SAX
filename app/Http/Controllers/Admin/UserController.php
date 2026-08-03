<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function create()
    {
        abort_unless(auth()->user()?->isMasterAdmin(), 403);

        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()?->isMasterAdmin(), 403);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'user_type' => ['required', 'integer', Rule::in(User::USER_TYPES)],
        ]);

        User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'user_type' => $request->user_type,
        ]);

        // limpa cache da lista de usuários após criar
        Cache::forget('users_all');

        return redirect()->route('admin.clients.index')->with('success', __('messages.user_created_successfully'));
    }

    public function updateType(Request $request, $id)
    {
        abort_unless(auth()->user()?->isMasterAdmin(), 403);

        $validated = $request->validate([
            'user_type' => ['required', 'integer', Rule::in(User::USER_TYPES)],
        ]);

        $user = User::findOrFail($id);
        $newType = (int) $validated['user_type'];

        if ($user->is(auth()->user()) && $newType !== User::TYPE_ADMIN_MASTER) {
            return back()->with('error', __('messages.cannot_change_own_master_role'));
        }

        if ($user->isMasterAdmin()
            && $newType !== User::TYPE_ADMIN_MASTER
            && User::where('user_type', User::TYPE_ADMIN_MASTER)->count() <= 1) {
            return back()->with('error', __('messages.cannot_remove_last_master'));
        }

        DB::transaction(function () use ($user, $newType) {
            $user->user_type = $newType;
            $user->save();
        });

        // limpa cache da lista de usuários após update
        Cache::forget('users_all');

        return redirect()->back()->with('success', __('messages.user_type_updated_successfully'));
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()?->isMasterAdmin(), 403);

        $user = User::findOrFail($id);

        if ($user->is(auth()->user())) {
            return back()->with('error', __('messages.cannot_delete_own_user'));
        }

        if ($user->isMasterAdmin()
            && User::where('user_type', User::TYPE_ADMIN_MASTER)->count() <= 1) {
            return back()->with('error', __('messages.cannot_remove_last_master'));
        }

        DB::transaction(function () use ($user) {
            $user->delete();
        });

        // limpa cache da lista de usuários após excluir
        Cache::forget('users_all');

        return redirect()->back()->with('success', 'Usuário excluído com sucesso.');
    }

    public function index(Request $request)
    {
        $query = User::query();

        // Filtro por tipo
        if ($request->filled('user_type')) {
            $query->where('user_type', $request->user_type);
        }

        // Filtro por nome ou email
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->orderBy('id', 'desc')->paginate(20);
        $userCount = $users->total();

        return view('admin.users.index', compact('users', 'userCount'));
    }
}
