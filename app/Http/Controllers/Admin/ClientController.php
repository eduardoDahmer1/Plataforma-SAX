<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class ClientController extends Controller
{
    public function index()
    {
        $users = User::whereIn('user_type', [1, 2, 3])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    
        $userCount = User::whereIn('user_type', [1, 2, 3])->count();
    
        return view('admin.clients.index', compact('users', 'userCount'));
    }    

    public function show($id)
    {
        $client = User::with(['orders.items'])->findOrFail($id);

        return view('admin.clients.show', compact('client'));
    }
}
