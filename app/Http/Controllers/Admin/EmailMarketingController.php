<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendEmailCampaign;
use App\Models\Contact;
use App\Models\EmailCampaign;
use App\Models\EmailTemplate;
use App\Services\EmailAudienceService;
use App\Services\EmailContentSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EmailMarketingController extends Controller
{
    public function create(Request $request, EmailAudienceService $audiences)
    {
        $contact = $request->filled('contact')
            ? Contact::query()->findOrFail($request->integer('contact'))
            : null;
        $selectedTemplate = $request->filled('template')
            ? EmailTemplate::query()->findOrFail($request->integer('template'))
            : null;
        $selectedContactIds = collect(explode(',', (string) $request->get('contacts')))
            ->filter(fn ($id) => ctype_digit($id))
            ->map(fn ($id) => (int) $id)
            ->unique()->take(100)->values();
        $selectedContacts = $selectedContactIds->isNotEmpty()
            ? Contact::query()->whereKey($selectedContactIds)->whereNotNull('email')->get(['id', 'name', 'email'])
            : collect();

        return view('admin.contacts.compose', [
            'templates' => EmailTemplate::query()->orderBy('name')->get(),
            'audiences' => EmailAudienceService::AUDIENCES,
            'audienceCounts' => $audiences->counts(),
            'replyContact' => $contact,
            'selectedTemplate' => $selectedTemplate,
            'selectedContacts' => $selectedContacts,
        ]);
    }

    public function send(
        Request $request,
        EmailAudienceService $audiences,
        EmailContentSanitizer $sanitizer,
    ): RedirectResponse {
        $data = $request->validate([
            'audience' => ['required', Rule::in(array_keys(EmailAudienceService::AUDIENCES))],
            'contact_id' => ['nullable', 'integer', 'exists:contacts,id'],
            'specific_emails' => ['nullable', 'string', 'max:30000'],
            'contact_ids' => ['nullable', 'array', 'max:100'],
            'contact_ids.*' => ['integer', 'distinct', 'exists:contacts,id'],
            'template_id' => ['nullable', 'integer', 'exists:email_templates,id'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:1000000'],
            'save_template' => ['nullable', 'boolean'],
            'template_name' => ['nullable', 'string', 'max:120', 'required_if:save_template,1'],
        ]);

        $contact = isset($data['contact_id']) ? Contact::query()->find($data['contact_id']) : null;
        if ($data['audience'] === 'reply' && ! $contact) {
            throw ValidationException::withMessages(['contact_id' => 'Selecione o contato que receberá a resposta.']);
        }
        if ($data['audience'] === 'specific' && blank($data['specific_emails'] ?? null)) {
            throw ValidationException::withMessages(['specific_emails' => 'Informe ao menos um e-mail.']);
        }
        if ($data['audience'] === 'selected_contacts' && empty($data['contact_ids'])) {
            throw ValidationException::withMessages(['contact_ids' => 'Selecione ao menos um contato na caixa de entrada.']);
        }

        $type = $data['audience'] === 'reply' ? 'reply' : 'marketing';
        $recipients = $audiences->resolve(
            $data['audience'],
            $contact,
            $data['specific_emails'] ?? null,
            $data['contact_ids'] ?? [],
            $type === 'marketing',
        );

        if ($recipients->isEmpty()) {
            throw ValidationException::withMessages([
                'audience' => 'Nenhum destinatário válido foi encontrado (ou todos cancelaram os envios promocionais).',
            ]);
        }

        $body = $sanitizer->sanitize($data['body']);
        if ($body === '' || (trim(strip_tags($body)) === '' && ! str_contains($body, '<img'))) {
            throw ValidationException::withMessages(['body' => 'Escreva o conteúdo do e-mail.']);
        }

        $campaign = DB::transaction(function () use ($data, $body, $recipients, $type, $contact, $request) {
            $templateId = $data['template_id'] ?? null;
            if ($request->boolean('save_template')) {
                $template = EmailTemplate::query()->create([
                    'name' => $data['template_name'],
                    'subject' => trim(strip_tags($data['subject'])),
                    'body' => $body,
                    'created_by' => $request->user()->id,
                    'updated_by' => $request->user()->id,
                ]);
                $templateId = $template->id;
            }

            $campaign = EmailCampaign::query()->create([
                'template_id' => $templateId,
                'contact_id' => $contact?->id,
                'created_by' => $request->user()->id,
                'type' => $type,
                'audience' => $data['audience'],
                'subject' => trim(strip_tags($data['subject'])),
                'body' => $body,
                'recipient_count' => $recipients->count(),
                'status' => 'pending',
            ]);

            $campaign->recipients()->createMany($recipients->map(fn ($recipient) => array_merge($recipient, [
                'unsubscribe_token' => Str::random(64),
            ]))->all());

            return $campaign;
        });

        SendEmailCampaign::dispatchAfterResponse($campaign->id);

        return redirect()->route('admin.contatos.index', ['view' => 'history'])
            ->with('success', "Envio #{$campaign->id} preparado para {$campaign->recipient_count} destinatário(s).");
    }

    public function templateCreate()
    {
        return view('admin.contacts.template-form', ['template' => new EmailTemplate]);
    }

    public function templateEdit(EmailTemplate $template)
    {
        return view('admin.contacts.template-form', compact('template'));
    }

    public function templateStore(Request $request, EmailContentSanitizer $sanitizer): RedirectResponse
    {
        $template = EmailTemplate::query()->create($this->templateData($request, $sanitizer) + [
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.email-templates.edit', $template)->with('success', 'Template criado com sucesso.');
    }

    public function templateUpdate(
        Request $request,
        EmailTemplate $template,
        EmailContentSanitizer $sanitizer,
    ): RedirectResponse {
        $template->update($this->templateData($request, $sanitizer) + ['updated_by' => $request->user()->id]);

        return back()->with('success', 'Template atualizado com sucesso.');
    }

    public function templateDestroy(EmailTemplate $template): RedirectResponse
    {
        $template->delete();

        return redirect()->route('admin.contatos.index', ['view' => 'templates'])->with('success', 'Template removido.');
    }

    private function templateData(Request $request, EmailContentSanitizer $sanitizer): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:1000000'],
        ]);
        $data['subject'] = trim(strip_tags($data['subject']));
        $data['body'] = $sanitizer->sanitize($data['body']);

        return $data;
    }
}
