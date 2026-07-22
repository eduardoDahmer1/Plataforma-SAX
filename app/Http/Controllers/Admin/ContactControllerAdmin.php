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
        $period = (string) $request->get('period', 'all');
        $period = array_key_exists($period, $this->periods()) ? $period : 'all';
        $periods = $this->periods();
        $perPage = (int) $request->get('per_page', 20);
        $perPage = in_array($perPage, [20, 30, 50, 100], true) ? $perPage : 20;

        [$from, $to, $periodLabel] = $this->periodRange($period);

        $filtroBusca = function ($q) use ($search) {
            $q->where(function ($sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        };

        $filteredContacts = Contact::query()
            ->when($from, fn ($q) => $q->whereBetween('created_at', [$from, $to]))
            ->when($search !== '', $filtroBusca);

        $contacts = (clone $filteredContacts)
            ->when($type, fn ($q) => $q->where('contact_type', $type))
            ->latest()
            ->paginate($perPage)
            ->appends($request->query());

        // Os contadores acompanham o período e a busca, mas não o tipo selecionado.
        $contagem = (clone $filteredContacts)
            ->selectRaw('contact_type, count(*) as total')
            ->groupBy('contact_type')
            ->pluck('total', 'contact_type');

        $totais = [
            'all' => $contagem->sum(),
            1     => $contagem[1] ?? 0,
            2     => $contagem[2] ?? 0,
            3     => $contagem[3] ?? 0,
        ];

        $stats = [
            'total' => (clone $filteredContacts)->count(),
            'consultas' => $totais[1],
            'curriculos' => $totais[2],
            'newsletters' => $totais[3],
        ];

        return view('admin.contacts.index', compact(
            'contacts', 'type', 'perPage', 'search', 'period', 'periodLabel', 'periods', 'totais', 'stats'
        ));
    }

    public function export(Request $request)
    {
        $period = (string) $request->get('period', 'all');
        $period = array_key_exists($period, $this->periods()) ? $period : 'all';

        return Excel::download(new ContactsExport($request->type, $period), 'contatos.xlsx');
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

    private function periods(): array
    {
        return [
            'all' => 'Todos',
            'today' => 'Hoje',
            'week' => 'Últimos 7 dias',
            'month' => 'Mês atual',
        ];
    }

    private function periodRange(string $period): array
    {
        return match ($period) {
            'today' => [now()->startOfDay(), now()->endOfDay(), $this->periods()['today']],
            'week' => [now()->subDays(6)->startOfDay(), now()->endOfDay(), $this->periods()['week']],
            'month' => [now()->startOfMonth(), now()->endOfDay(), $this->periods()['month']],
            default => [null, null, $this->periods()['all']],
        };
    }
}
