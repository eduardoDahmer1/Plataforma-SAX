<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContactController extends Controller
{
    public function showForm()
    {
        return view('contact.form');
    }

    public function store(Request $request)
    {
        $type = (int) $request->input('contact_type');

        $rules = [
            'name' => 'required|string|max:255',
            'contact_type' => 'required|in:1,2',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
            'message' => $type === 1 ? 'required|string' : 'nullable|string',
            'attachment' => $type === 2 ? 'required|file|mimes:pdf,jpg,jpeg,png|max:2048' : 'nullable',
        ];

        $validated = $request->validate($rules);

        if ($request->hasFile('attachment')) {
            $validated['attachment'] = $request->file('attachment')
                ->store('attachments', 'public');
        }

        Contact::create($validated);

        return redirect()->back()->with('success', 'Mensagem enviada com sucesso!');
    }
}
