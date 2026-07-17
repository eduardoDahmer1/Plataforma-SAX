<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Policy;
use Illuminate\Http\Request;

class PolicyControllerAdmin extends Controller
{
    public function index()
    {
        return view('admin.policies.index', ['policies' => Policy::orderBy('id')->get()]);
    }

    public function edit(Policy $policy)
    {
        return view('admin.policies.edit', compact('policy'));
    }

    public function update(Request $request, Policy $policy)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $policy->update($data);

        return redirect()->route('admin.policies.index')->with('success', 'Política atualizada com sucesso.');
    }
}
