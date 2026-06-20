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
        $type = $request->get('type');
        $perPage = $request->get('per_page', 20);

        $contacts = Contact::query()
            ->when($type, fn($q) => $q->where('contact_type', $type))
            ->latest()
            ->paginate($perPage)
            ->appends($request->query());

        return view('admin.contacts.index', compact('contacts', 'type', 'perPage'));
    }

    public function export(Request $request)
    {
        return Excel::download(new ContactsExport($request->type), 'contatos.xlsx');
    }

    public function destroy($id)
    {
        $contact = Contact::findOrFail($id);
        
        if ($contact->attachment) {
            Storage::disk('public')->delete($contact->attachment);
        }
        
        $contact->delete();

        return response()->json(['success' => true]);
    }
}