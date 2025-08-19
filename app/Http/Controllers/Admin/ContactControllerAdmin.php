<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Exports\ContactsExport;
use Maatwebsite\Excel\Facades\Excel;


class ContactControllerAdmin extends Controller
{
    public function index(Request $request)
    {
        $query = Contact::query();
    
        if ($request->has('type') && in_array($request->type, ['1', '2'])) {
            $query->where('contact_type', $request->type);
        }
    
        $contacts = $query->latest()->paginate(10);
        $selectedType = $request->type;
    
        return view('admin.contacts.index', compact('contacts', 'selectedType'));

    }
    
    public function export(\Illuminate\Http\Request $request)
    {
        $type = $request->type;
        return Excel::download(new ContactsExport($type), 'contatos.xlsx');
    }

    public function destroy(Contact $contact)
    {
        if ($contact->attachment) {
            Storage::disk('public')->delete($contact->attachment);
        }
        $contact->delete();

        return redirect()->back()->with('success', 'Mensagem excluída com sucesso!');
    }
}
