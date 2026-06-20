<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        $query = User::whereIn('user_type', [1, 2, 3]);

        if ($request->filled('user_type')) {
            $query->where('user_type', $request->user_type);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate($perPage);
        $userCount = User::whereIn('user_type', [1, 2, 3])->count();

        return view('admin.clients.index', compact('users', 'userCount', 'perPage'));
    }    

    public function show($id)
    {
        $client = User::with(['orders.items'])->findOrFail($id);
        return view('admin.clients.show', compact('client'));
    }
}