<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserAccountSafetyService;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request, UserAccountSafetyService $safety)
    {
        $validated = $request->validate([
            'account_filter' => ['nullable', 'in:suspicious,unverified,verified,without_orders'],
        ]);
        $perPage = (int) $request->get('per_page', 20);
        $perPage = in_array($perPage, [20, 30, 40, 100], true) ? $perPage : 20;
        $query = User::whereIn('user_type', User::USER_TYPES)->withCount('orders');

        if ($request->filled('user_type')) {
            $query->where('user_type', $request->user_type);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%');
            });
        }

        $safety->applyFilter($query, $validated['account_filter'] ?? null);

        $users = $query->orderBy('created_at', 'desc')->paginate($perPage);
        $users->getCollection()->each(function (User $user) use ($safety) {
            $user->setAttribute('suspicion_reasons', $safety->reasons($user));
        });
        $userCount = User::whereIn('user_type', User::USER_TYPES)->count();

        return view('admin.clients.index', compact('users', 'userCount', 'perPage'));
    }

    public function show($id)
    {
        $client = User::with(['orders' => fn ($q) => $q->latest(), 'orders.items', 'orders.cupon', 'abandonedCarts' => fn ($q) => $q->latest('abandoned_at')])
            ->findOrFail($id);

        return view('admin.clients.show', compact('client'));
    }
}
