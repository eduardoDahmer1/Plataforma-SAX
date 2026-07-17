<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ContactsExport;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ContactControllerAdmin extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type');
        $search = trim((string) $request->get('search'));
        $perPage = (int) $request->get('per_page', 20);

        $filtroBusca = function ($q) use ($search) {
            $q->where(function ($sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        };

        $contacts = Contact::query()
            ->when($type, fn ($q) => $q->where('contact_type', $type))
            ->when($search !== '', $filtroBusca)
            ->latest()
            ->paginate($perPage)
            ->appends($request->query());

        // Contadores das abas: acompanham a busca, mas não o tipo selecionado.
        $contagem = Contact::query()
            ->when($search !== '', $filtroBusca)
            ->selectRaw('contact_type, count(*) as total')
            ->groupBy('contact_type')
            ->pluck('total', 'contact_type');

        $totais = [
            'all' => $contagem->sum(),
            1     => $contagem[1] ?? 0,
            2     => $contagem[2] ?? 0,
            3     => $contagem[3] ?? 0,
        ];

        return view('admin.contacts.index', compact('contacts', 'type', 'perPage', 'search', 'totais'));
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

        return response()->json([
            'success' => true,
            'message' => __('messages.contato_removido'),
        ]);
    }
}
