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
        ];

        if ($type === 1) { // Fale Conosco
            $rules['message'] = 'required|string';
        } else { // Currículo
            $rules['attachment'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:2048';
            $rules['message'] = 'nullable|string';
        }

        $validated = $request->validate($rules);

        $attachmentPath = null;
        if ($type === 2 && $request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('attachments', 'public');
        }

        Contact::create([
            'name' => $validated['name'],
            'contact_type' => $type,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'message' => $validated['message'] ?? null,
            'attachment' => $attachmentPath,
        ]);

        return redirect()->back()->with('success', 'Mensagem enviada com sucesso!');
    }
}
