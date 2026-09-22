<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ContactsExport;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\EmailCampaign;
use App\Models\EmailTemplate;
use App\Models\ResumeForwardAttempt;
use App\Services\ResumeForwardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class ContactControllerAdmin extends Controller
{
    public function index(Request $request)
    {
        $panel = in_array($request->get('view'), ['inbox', 'templates', 'history', 'hr-queue', 'hr-history'], true)
            ? $request->get('view')
            : 'inbox';
        $type = $request->get('type');
        $search = trim((string) $request->get('search'));
        $period = (string) $request->get('period', 'all');
        $period = array_key_exists($period, $this->periods()) ? $period : 'all';
        $status = in_array($request->get('status'), ['all', 'unread', 'read'], true)
            ? $request->get('status')
            : 'all';
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
            ->when($status === 'unread', fn ($q) => $q->whereNull('read_at'))
            ->when($status === 'read', fn ($q) => $q->whereNotNull('read_at'))
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
            1 => $contagem[1] ?? 0,
            2 => $contagem[2] ?? 0,
            3 => $contagem[3] ?? 0,
            4 => $contagem[4] ?? 0,
        ];

        $stats = [
            'total' => (clone $filteredContacts)->count(),
            'consultas' => $totais[1],
            'curriculos' => $totais[2],
            'newsletters' => $totais[3],
            'optical' => $totais[4],
            'unread' => (clone $filteredContacts)->whereNull('read_at')->count(),
        ];

        $emailTemplates = $panel === 'templates'
            ? EmailTemplate::query()->with('creator')->latest('updated_at')->paginate(12, ['*'], 'templates_page')
            : collect();
        $campaigns = $panel === 'history'
            ? EmailCampaign::query()->with(['creator', 'template'])->latest()->paginate(20, ['*'], 'campaigns_page')
            : collect();
        $hrAttempts = match ($panel) {
            'hr-queue' => ResumeForwardAttempt::query()->with('initiator:id,name')->whereIn('status', ['queued', 'processing'])
                ->orderBy('created_at')->paginate(20, ['*'], 'hr_page'),
            'hr-history' => ResumeForwardAttempt::query()->with(['initiator:id,name', 'canceler:id,name'])
                ->latest()->paginate(20, ['*'], 'hr_page'),
            default => collect(),
        };

        return view('admin.contacts.index', compact(
            'contacts', 'type', 'status', 'perPage', 'search', 'period', 'periodLabel', 'periods', 'totais', 'stats',
            'panel', 'emailTemplates', 'campaigns', 'hrAttempts'
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

    public function read(Contact $contact): JsonResponse
    {
        if (! $contact->read_at) {
            $contact->update(['read_at' => now()]);
        }

        return response()->json(['success' => true, 'read_at' => $contact->read_at?->toISOString()]);
    }

    public function markAllRead(): RedirectResponse
    {
        $updated = Contact::query()->whereNull('read_at')->update(['read_at' => now()]);

        return back()->with('success', "{$updated} mensagem(ns) marcada(s) como lida(s).");
    }

    public function sendToHr(Request $request, Contact $contact, ResumeForwardService $resumes): RedirectResponse
    {
        abort_unless((int) $contact->contact_type === 2, 404);

        if ($contact->hr_sent_at) {
            return back()->with('success', 'Este currículo já foi enviado ao RH.');
        }

        if (! $contact->store_name) {
            $data = $request->validate(['store_name' => ['required', Rule::in(array_values(Contact::STORES))]]);
            $contact->update(['store_name' => $data['store_name']]);
        }

        return $resumes->queue($contact, $request->user()?->id)
            ? back()->with('success', 'Currículo colocado na fila de envio ao RH de '.$contact->store_name.'.')
                ->with('hr_progress_ids', [$contact->id])
            : back()->with('error', 'Não foi possível colocar o currículo na fila ou ele já está aguardando envio.');
    }

    public function hrProgress(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'string', 'regex:/^[1-9][0-9]*(,[1-9][0-9]*){0,99}$/', 'max:2100'],
        ]);
        $ids = array_values(array_unique(array_map('intval', explode(',', $data['ids']))));
        $contacts = Contact::query()
            ->whereKey($ids)
            ->where('contact_type', 2)
            ->get(['id', 'hr_sent_at', 'hr_sent_to', 'hr_last_error'])
            ->keyBy('id');
        $latestAttempts = ResumeForwardAttempt::query()->whereIn('contact_id', $ids)
            ->orderByDesc('id')->get(['contact_id', 'status'])
            ->unique('contact_id')->keyBy('contact_id');

        $statuses = [];
        $sent = 0;
        $failed = 0;
        $canceled = 0;
        foreach ($ids as $id) {
            $contact = $contacts->get($id);
            $status = $contact?->hr_sent_at ? 'sent'
                : (($contact?->hr_last_error || ! $contact) ? 'failed'
                    : ($latestAttempts->get($id)?->status === 'canceled' ? 'canceled' : 'pending'));
            $statuses[$id] = $status;
            $sent += (int) ($status === 'sent');
            $failed += (int) ($status === 'failed');
            $canceled += (int) ($status === 'canceled');
        }

        return response()->json([
            'total' => count($ids),
            'sent' => $sent,
            'failed' => $failed,
            'canceled' => $canceled,
            'pending' => count($ids) - $sent - $failed - $canceled,
            'statuses' => $statuses,
        ]);
    }

    public function cancelHrAttempt(Request $request, ResumeForwardAttempt $attempt): RedirectResponse
    {
        $canceled = DB::transaction(function () use ($request, $attempt) {
            $contact = Contact::query()->whereKey($attempt->contact_id)->lockForUpdate()->first();
            $updated = ResumeForwardAttempt::query()->whereKey($attempt->id)
                ->where('status', 'queued')
                ->update([
                    'status' => 'canceled',
                    'canceled_by' => $request->user()->id,
                    'finished_at' => now(),
                ]);

            if ($updated && $contact && ! $contact->hr_sent_at) {
                $contact->update(['hr_attempted_at' => null, 'hr_last_error' => null]);
            }

            return (bool) $updated;
        });

        return $canceled
            ? back()->with('success', 'Envio retirado da fila. O currículo pode ser enviado novamente.')
            : back()->with('error', 'Este envio já começou ou saiu da fila. Atualize a lista para ver o estado.');
    }

    public function bulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(['mark_read', 'mark_unread', 'delete', 'email', 'send_hr'])],
            'contact_ids' => ['required', 'array', 'min:1', 'max:100'],
            'contact_ids.*' => ['integer', 'distinct', 'exists:contacts,id'],
            'store_names' => ['sometimes', 'array'],
            'store_names.*' => ['nullable', Rule::in(array_values(Contact::STORES))],
        ]);
        $ids = array_values(array_unique(array_map('intval', $data['contact_ids'])));

        if ($data['action'] === 'email') {
            return redirect()->route('admin.emails.create', ['contacts' => implode(',', $ids)]);
        }

        if ($data['action'] === 'send_hr') {
            $resumes = app(ResumeForwardService::class);
            $queued = 0;
            $failed = 0;
            $queuedIds = [];
            foreach (Contact::query()->whereKey($ids)->where('contact_type', 2)->whereNull('hr_sent_at')->get() as $contact) {
                if (! $contact->store_name) {
                    $storeName = $data['store_names'][$contact->id] ?? null;
                    if (! $storeName) {
                        $failed++;
                        continue;
                    }
                    $contact->update(['store_name' => $storeName]);
                }
                if ($resumes->queue($contact, $request->user()?->id)) {
                    $queued++;
                    $queuedIds[] = $contact->id;
                } else {
                    $failed++;
                }
            }

            return back()->with($failed ? 'error' : 'success', "{$queued} currículo(s) colocado(s) na fila do RH; {$failed} não processado(s).")
                ->with('hr_progress_ids', $queuedIds);
        }

        if ($data['action'] === 'delete') {
            $contacts = Contact::query()->whereKey($ids)->get();
            foreach ($contacts as $contact) {
                if ($contact->attachment) {
                    Storage::disk('public')->delete($contact->attachment);
                }
            }
            Contact::query()->whereKey($ids)->delete();

            return back()->with('success', $contacts->count().' contato(s) excluído(s).');
        }

        Contact::query()->whereKey($ids)->update([
            'read_at' => $data['action'] === 'mark_read' ? now() : null,
        ]);

        return back()->with('success', count($ids).' mensagem(ns) atualizada(s).');
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
