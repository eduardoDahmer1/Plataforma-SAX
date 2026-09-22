<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\JobFlyer;
use App\Models\Language;
use App\Models\WhatsappContact;
use App\Services\StoreControlService;
use App\Services\ResumeForwardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class ContactController extends Controller
{
    public function showForm(StoreControlService $storeControls)
    {
        $locale = session('locale', config('app.locale'));

        App::setLocale($locale);

        $lang = Cache::remember('contact_languages', now()->addHours(24), fn () => Language::all());
        $flyers = Cache::remember('contact_active_job_flyers', now()->addMinutes(10), fn () =>
            JobFlyer::active()->orderBy('sort_order')->get()
        );
        $directoryContacts = WhatsappContact::query()
            ->where('active', true)
            ->where('show_on_contact_page', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
        $isOtica = $storeControls->isOtica();
        $contactGuideEnabled = $storeControls->navigationVisible('header', 'guide')
            || $storeControls->navigationVisible('footer', 'guide');

        return view('contact.form', compact('lang', 'locale', 'flyers', 'directoryContacts', 'isOtica', 'contactGuideEnabled'));
    }

    public function store(Request $request, StoreControlService $storeControls, ResumeForwardService $resumes)
    {
        $isOtica = $storeControls->isOtica();
        $type = $isOtica ? 4 : (int) $request->input('contact_type');
        $request->merge(['contact_type' => $type]);

        $rules = [
            'name' => 'required|string|max:255',
            'contact_type' => ['required', $isOtica ? 'in:4' : 'in:1,2'],
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
            'message' => 'required|string',
            'store_name' => $type === 2
                ? ['required', Rule::in(array_values(Contact::STORES))]
                : ['nullable', 'string', 'max:255'],
            'attachment' => match ($type) {
                2 => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
                4 => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                default => 'nullable',
            },
        ];

        $validated = $request->validate($rules);

        if ($request->hasFile('attachment')) {
            $validated['attachment'] = $request->file('attachment')
                ->store('attachments', 'public');
        }

        $contact = Contact::create($validated);
        if ($type === 2) {
            $resumes->send($contact);
        }

        $successMsg = __('messages.mensagem_sucesso') ?? 'Mensagem enviada com sucesso!';

        return redirect()->back()->with('success', $successMsg);
    }
}
