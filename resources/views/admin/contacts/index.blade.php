@extends('layout.admin')

@push('styles')
<link href="{{ asset('css/email-marketing.css') }}?v={{ filemtime(public_path('css/email-marketing.css')) }}" rel="stylesheet">
@endpush

@push('scripts')
<script src="{{ asset('js/resume-progress.js') }}?v={{ filemtime(public_path('js/resume-progress.js')) }}" defer></script>
@endpush

@section('content')
@php
    $tipos = [
        1 => ['rotulo' => __('messages.contato_tipo_consulta'), 'cor' => '#3b6fd4', 'icone' => 'fa-comment'],
        2 => ['rotulo' => __('messages.contato_tipo_curriculo'), 'cor' => '#1f7a37', 'icone' => 'fa-file-lines'],
        3 => ['rotulo' => __('messages.contato_tipo_newsletter'), 'cor' => '#9a7b1f', 'icone' => 'fa-envelope'],
        4 => ['rotulo' => 'Atendimento óptico', 'cor' => '#7b5ca8', 'icone' => 'fa-glasses'],
    ];
@endphp

<x-admin.card>
    <div class="sax-cat sax-msg" id="msg-app" data-delete-url="{{ url('admin/contatos') }}" data-read-url="{{ route('admin.contacts.read', '__ID__') }}" data-confirm-delete="{{ __('messages.confirmar_exclusao') }}" data-error-message="{{ __('messages.activate_erro') }}">

        <x-admin.alert />

        @if($panel === 'inbox' && count(session('hr_progress_ids', [])) > 0)
            <section class="resume-progress" id="resumeProgress" data-url="{{ route('admin.contacts.hr-progress') }}" data-ids="{{ implode(',', session('hr_progress_ids', [])) }}" aria-label="Progresso do envio de currículos ao RH">
                <div class="resume-progress__heading">
                    <strong>Enviando currículos ao RH</strong>
                    <span id="resumeProgressCount" role="status" aria-live="polite">0 de {{ count(session('hr_progress_ids', [])) }} enviados</span>
                </div>
                <div class="resume-progress__track" role="progressbar" aria-label="Currículos enviados" aria-valuemin="0" aria-valuemax="{{ count(session('hr_progress_ids', [])) }}" aria-valuenow="0">
                    <div class="resume-progress__fill" id="resumeProgressFill"></div>
                </div>
                <p class="resume-progress__detail" id="resumeProgressDetail">Aguardando a atualização dos envios…</p>
            </section>
        @endif

        {{-- Cabeçalho --}}
        <div class="sax-cat__top">
            <div>
                <h1 class="sax-cat__title">{{ __('messages.centro_mensagens_titulo') }}</h1>
                <span class="sax-cat__sub">{{ __('messages.gestao_comunicacoes_desc') }}</span>
            </div>

            @if($panel === 'inbox')
                <a href="{{ route('admin.emails.create') }}" class="sax-cat__new"><i class="fas fa-paper-plane"></i> Novo e-mail</a>
            @elseif($panel === 'templates')
                <a href="{{ route('admin.email-templates.create') }}" class="sax-cat__new"><i class="fas fa-plus"></i> Novo template</a>
            @elseif(in_array($panel, ['hr-queue', 'hr-history']))
                <a href="{{ route('admin.contatos.index', ['type' => 2]) }}" class="sax-cat__new"><i class="fas fa-file-lines"></i> Ver currículos</a>
            @else
                <a href="{{ route('admin.emails.create') }}" class="sax-cat__new"><i class="fas fa-paper-plane"></i> Novo envio</a>
            @endif
        </div>

        <nav class="email-center-nav" aria-label="Áreas da central de mensagens">
            <a href="{{ route('admin.contatos.index') }}" class="{{ $panel === 'inbox' ? 'is-active' : '' }}"><i class="fa-solid fa-inbox"></i> Recebidos</a>
            <a href="{{ route('admin.emails.create') }}"><i class="fa-solid fa-pen-to-square"></i> Criar e-mail</a>
            <a href="{{ route('admin.contatos.index', ['view' => 'templates']) }}" class="{{ $panel === 'templates' ? 'is-active' : '' }}"><i class="fa-solid fa-layer-group"></i> Templates</a>
            <a href="{{ route('admin.contatos.index', ['view' => 'history']) }}" class="{{ $panel === 'history' ? 'is-active' : '' }}"><i class="fa-solid fa-clock-rotate-left"></i> Histórico e-mail</a>
            <a href="{{ route('admin.contatos.index', ['view' => 'hr-queue']) }}" class="{{ $panel === 'hr-queue' ? 'is-active' : '' }}"><i class="fa-solid fa-list-check"></i> Fila RH</a>
            <a href="{{ route('admin.contatos.index', ['view' => 'hr-history']) }}" class="{{ $panel === 'hr-history' ? 'is-active' : '' }}"><i class="fa-solid fa-clock-rotate-left"></i> Histórico RH</a>
            @if($panel === 'inbox')
                <a href="{{ route('admin.contacts.export', ['type' => $type, 'period' => $period]) }}"><i class="fas fa-download"></i> Exportar</a>
            @endif
        </nav>

        @if($panel === 'inbox')

        <div class="contacts-period-panel">
            <div>
                <span class="contacts-period-eyebrow">Resumo dos contatos</span>
                <strong>{{ $periodLabel }}</strong>
            </div>
            <div class="contacts-period-actions">
                @foreach ($periods as $periodKey => $periodName)
                    <a href="{{ route('admin.contatos.index', ['period' => $periodKey, 'type' => $type, 'status' => $status, 'search' => $search, 'per_page' => $perPage]) }}"
                       class="contacts-period-btn {{ $period === $periodKey ? 'is-active' : '' }}">
                        {{ $periodName }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="contacts-summary-grid">
            @php
                $summaryCards = [
                    ['Total recebidos', $stats['total'], 'fa-inbox', '#2970ff'],
                    ['Consultas', $stats['consultas'], 'fa-comment', '#12b76a'],
                    ['Currículos', $stats['curriculos'], 'fa-file-lines', '#b7791f'],
                    ['Newsletter', $stats['newsletters'], 'fa-envelope', '#7f56d9'],
                    ['Ótica', $stats['optical'], 'fa-glasses', '#7b5ca8'],
                    ['Não lidas', $stats['unread'], 'fa-envelope-open-text', '#e5484d'],
                ];
            @endphp
            @foreach ($summaryCards as [$label, $value, $icon, $color])
                <div class="contacts-summary-card">
                    <span class="contacts-summary-icon" style="--summary-color: {{ $color }}"><i class="fa-solid {{ $icon }}"></i></span>
                    <span class="contacts-summary-label">{{ $label }}</span>
                    <strong>{{ number_format($value, 0, ',', '.') }}</strong>
                </div>
            @endforeach
        </div>

        {{-- Busca + abas por tipo + itens por página --}}
        <div class="sax-cat__bar sax-msg__bar">
            <form method="GET" action="{{ route('admin.contatos.index') }}" class="sax-cat__search">
                <i class="fa fa-search"></i>
                <input type="text" name="search" value="{{ $search }}" autocomplete="off"
                       placeholder="{{ __('messages.contato_buscar_placeholder') }}">
                <input type="hidden" name="period" value="{{ $period }}">
                @if ($status !== 'all') <input type="hidden" name="status" value="{{ $status }}"> @endif
                @if ($type)
                    <input type="hidden" name="type" value="{{ $type }}">
                @endif
                <input type="hidden" name="per_page" value="{{ $perPage }}">
            </form>

            <div class="sax-msg__tabs">
                <a href="{{ route('admin.contatos.index', ['period' => $period, 'status' => $status, 'search' => $search, 'per_page' => $perPage]) }}"
                   class="sax-chip {{ !$type ? 'is-on' : '' }}">
                    {{ __('messages.cupon_situacao_todas') }} <span>{{ $totais['all'] }}</span>
                </a>

                @foreach ($tipos as $id => $info)
                    <a href="{{ route('admin.contatos.index', ['period' => $period, 'type' => $id, 'status' => $status, 'search' => $search, 'per_page' => $perPage]) }}"
                       class="sax-chip {{ (int) $type === $id ? 'is-on' : '' }}">
                        {{ $info['rotulo'] }} <span>{{ $totais[$id] }}</span>
                    </a>
                @endforeach
                <a href="{{ route('admin.contatos.index', ['period' => $period, 'type' => $type, 'status' => $status === 'unread' ? 'all' : 'unread', 'search' => $search, 'per_page' => $perPage]) }}"
                   class="sax-chip {{ $status === 'unread' ? 'is-on' : '' }}">
                    <i class="fa-regular fa-envelope"></i> NÃO LIDAS <span>{{ $stats['unread'] }}</span>
                </a>
            </div>

            <form method="GET" action="{{ route('admin.contatos.index') }}" class="sax-msg__per">
                <input type="hidden" name="period" value="{{ $period }}">
                @if ($status !== 'all') <input type="hidden" name="status" value="{{ $status }}"> @endif
                @if ($type) <input type="hidden" name="type" value="{{ $type }}"> @endif
                @if ($search) <input type="hidden" name="search" value="{{ $search }}"> @endif
                <label for="contactsPerPage">Mostrar</label>
                <select id="contactsPerPage" name="per_page" onchange="this.form.submit()" aria-label="Quantidade de contatos por página">
                    @foreach ([20, 30, 50, 100] as $opt)
                        <option value="{{ $opt }}" @selected($perPage == $opt)>{{ $opt }}</option>
                    @endforeach
                </select>
                <span>por página</span>
            </form>

            <span class="sax-msg__hint" id="msg-feedback" role="status"></span>
        </div>

        <form method="POST" action="{{ route('admin.contacts.bulk') }}" id="contactsBulkForm" class="contacts-bulk-bar">
            @csrf
            <label class="contacts-check-all"><input type="checkbox" id="contactsSelectAll"> <span>Selecionar página</span></label>
            <strong id="contactsSelectedCount">0 selecionados</strong>
            <div class="contacts-bulk-actions">
                <button type="submit" name="action" value="send_hr" disabled data-bulk-action><i class="fa-solid fa-file-arrow-up"></i> Enviar currículos ao RH</button>
                <button type="submit" name="action" value="email" disabled data-bulk-action><i class="fa-solid fa-paper-plane"></i> Enviar e-mail</button>
                <button type="submit" name="action" value="mark_read" disabled data-bulk-action><i class="fa-solid fa-envelope-open"></i> Marcar lidos</button>
                <button type="submit" name="action" value="mark_unread" disabled data-bulk-action><i class="fa-solid fa-envelope"></i> Marcar não lidos</button>
                <button type="button" disabled data-bulk-action id="contactsCopyEmails"><i class="fa-solid fa-copy"></i> Copiar e-mails</button>
                <button type="submit" name="action" value="delete" class="is-danger" disabled data-bulk-action><i class="fa-solid fa-trash"></i> Excluir</button>
            </div>
            <div class="contacts-view-actions">
                <button type="button" id="contactsExpandAll"><i class="fa-solid fa-angles-down"></i> Abrir todas</button>
            </div>
        </form>
        <form method="POST" action="{{ route('admin.contacts.read-all') }}" id="contactsReadAllForm" class="contacts-read-all-form">
            @csrf
            <button type="submit" @disabled($stats['unread'] === 0)><i class="fa-solid fa-check-double"></i> Marcar todas como lidas</button>
        </form>

        {{-- Lista --}}
        <div class="sax-msg__list">
            @forelse ($contacts as $contact)
                @php
                    $info = $tipos[$contact->contact_type] ?? ['rotulo' => '—', 'cor' => '#999', 'icone' => 'fa-circle'];
                    $anexoUrl = $contact->attachment ? asset('storage/' . $contact->attachment) : null;
                    $extensao = $contact->attachment ? strtoupper(pathinfo($contact->attachment, PATHINFO_EXTENSION)) : null;
                @endphp

                <article class="sax-msg__item {{ $contact->read_at ? '' : 'is-unread' }}" data-row data-id="{{ $contact->id }}" data-email="{{ $contact->email }}">
                    <div class="sax-msg__main" data-toggle role="button" tabindex="0"
                         aria-expanded="false" title="{{ __('messages.contato_ver_completo') }}">

                        <label class="contacts-row-check" title="Selecionar contato" data-no-toggle>
                            <input type="checkbox" name="contact_ids[]" value="{{ $contact->id }}" form="contactsBulkForm" data-contact-check>
                        </label>

                        <span class="sax-msg__type" style="--cor: {{ $info['cor'] }}" title="{{ $info['rotulo'] }}">
                            <i class="fa-solid {{ $info['icone'] }}"></i>
                        </span>

                        <div class="sax-msg__who">
                            <span class="sax-msg__name">{{ $contact->name ?: '—' }} @if(!$contact->read_at)<em class="contacts-unread-dot">Nova</em>@endif</span>
                            @if ((int) $contact->contact_type === 2)
                                <span class="sax-msg__contactinfo" data-hr-status>
                                    @if ($contact->hr_sent_at)
                                        <strong>Enviado pro RH</strong> · {{ $contact->hr_sent_to }} · {{ $contact->hr_sent_at->format('d/m/Y H:i') }}
                                    @elseif ($contact->hr_last_error)
                                        <strong>Falha no envio ao RH</strong>
                                    @elseif ($contact->hr_attempted_at)
                                        <strong>Aguardando envio ao RH</strong>
                                    @else
                                        <strong>Pendente de envio ao RH</strong>
                                    @endif
                                </span>
                            @endif
                            <span class="sax-msg__contactinfo">
                                {{ $contact->email }}
                                @if ($contact->phone) · {{ $contact->phone }} @endif
                            </span>
                        </div>

                        {{-- A mensagem é sempre escapada: muitos envios são spam com HTML.
                             Inscrições de newsletter chegam sem texto, daí o cast. --}}
                        <p class="sax-msg__preview">{{ Str::limit((string) $contact->message, 110) }}</p>

                        <div class="sax-msg__side">
                            @if ($anexoUrl)
                                <span class="sax-msg__clip" title="{{ __('messages.contato_tem_anexo') }}">
                                    <i class="fa fa-paperclip"></i> {{ $extensao }}
                                </span>
                            @endif
                            <span class="sax-msg__date">{{ $contact->created_at->format('d/m/Y H:i') }}</span>
                        </div>

                        <i class="fa fa-chevron-down sax-msg__caret"></i>
                    </div>

                    {{-- Detalhe: só aparece ao clicar --}}
                    <div class="sax-msg__detail" hidden>
                        <div class="sax-msg__cols">
                            <div>
                                <span class="sax-msg__label">{{ __('messages.contato_tipo') }}</span>
                                <span class="sax-msg__value">{{ $info['rotulo'] }}</span>
                            </div>
                            @if ($contact->store_name)
                                <div>
                                    <span class="sax-msg__label">Loja</span>
                                    <span class="sax-msg__value">{{ $contact->store_name }}</span>
                                </div>
                            @elseif ((int) $contact->contact_type === 2)
                                <div>
                                    <span class="sax-msg__label">Loja não registrada · escolha para envio em lote</span>
                                    <select name="store_names[{{ $contact->id }}]" form="contactsBulkForm" class="form-select form-select-sm" data-no-toggle>
                                        <option value="">Escolher loja</option>
                                        @foreach (\App\Models\Contact::STORES as $storeName)
                                            <option value="{{ $storeName }}">{{ $storeName }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            @if ((int) $contact->contact_type === 2)
                                <div>
                                    <span class="sax-msg__label">Encaminhamento ao RH</span>
                                    <span class="sax-msg__value">
                                        @if ($contact->hr_sent_at)
                                            Enviado pro RH em {{ $contact->hr_sent_at->format('d/m/Y H:i') }} para {{ $contact->hr_sent_to }}
                                        @elseif ($contact->hr_last_error)
                                            Falha: {{ $contact->hr_last_error ?: 'Verifique o envio.' }}
                                        @elseif ($contact->hr_attempted_at)
                                            Aguardando processamento desde {{ $contact->hr_attempted_at->format('d/m/Y H:i') }}
                                        @else
                                            Ainda não enviado
                                        @endif
                                    </span>
                                </div>
                            @endif
                            <div>
                                <span class="sax-msg__label">E-mail</span>
                                <a class="sax-msg__value" href="mailto:{{ $contact->email }}">{{ $contact->email ?: '—' }}</a>
                            </div>
                            <div>
                                <span class="sax-msg__label">{{ __('messages.telefone') }}</span>
                                @if ($contact->phone)
                                    <a class="sax-msg__value" target="_blank" rel="noopener"
                                       href="https://wa.me/{{ preg_replace('/\D/', '', $contact->phone) }}">
                                        <i class="fab fa-whatsapp"></i> {{ $contact->phone }}
                                    </a>
                                @else
                                    <span class="sax-msg__value">—</span>
                                @endif
                            </div>
                            <div>
                                <span class="sax-msg__label">{{ __('messages.cupon_col_data') }}</span>
                                <span class="sax-msg__value">{{ $contact->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>

                        <span class="sax-msg__label">{{ __('messages.contato_mensagem') }}</span>
                        <div class="sax-msg__text">{{ $contact->message ?: '—' }}</div>

                        @if ($anexoUrl)
                            <span class="sax-msg__label">
                                {{ (int) $contact->contact_type === 4 ? 'Receita óptica' : __('messages.contato_anexo') }}
                            </span>
                            <div class="sax-msg__file">
                                <i class="fa fa-file"></i>
                                <span>{{ basename($contact->attachment) }}</span>
                                <a href="{{ $anexoUrl }}" target="_blank" rel="noopener" class="sax-cat-act">
                                    <i class="fa fa-eye"></i> {{ __('messages.contato_abrir') }}
                                </a>
                                <a href="{{ $anexoUrl }}" download class="sax-cat-act">
                                    <i class="fa fa-download"></i> {{ __('messages.contato_baixar') }}
                                </a>
                            </div>
                        @endif

                        <div class="sax-msg__actions">
                            @if ((int) $contact->contact_type === 2 && ! $contact->hr_sent_at && (! $contact->hr_attempted_at || $contact->hr_last_error || $contact->hr_attempted_at->lt(now()->subMinutes(30))))
                                <form method="POST" action="{{ route('admin.contacts.send-hr', $contact) }}" data-no-toggle>
                                    @csrf
                                    @if (! $contact->store_name)
                                        <select name="store_name" class="form-select form-select-sm" required aria-label="Loja para encaminhar currículo">
                                            <option value="">Escolher loja</option>
                                            @foreach (\App\Models\Contact::STORES as $storeName)
                                                <option value="{{ $storeName }}">{{ $storeName }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                    <button type="submit" class="sax-cat-act"><i class="fa-solid fa-file-arrow-up"></i> Enviar pro RH</button>
                                </form>
                            @endif
                            @if ($contact->email)
                                <a href="{{ route('admin.emails.create', ['contact' => $contact->id]) }}" class="sax-cat-act">
                                    <i class="fa fa-reply"></i> {{ __('messages.contato_responder') }}
                                </a>
                            @endif
                            <button type="button" class="sax-cat-act sax-cat-act--danger" data-del>
                                <i class="fa fa-trash"></i> {{ __('messages.eliminar') }}
                            </button>
                        </div>
                    </div>
                </article>
            @empty
                <p class="sax-cat__empty">{{ __('messages.contato_nenhum') }}</p>
            @endforelse
        </div>

        <div class="sax-cat__pag">{{ $contacts->links() }}</div>
        @elseif(in_array($panel, ['hr-queue', 'hr-history']))
            <div class="hr-attempts-head">
                <div>
                    <h2>{{ $panel === 'hr-queue' ? 'Fila de currículos do RH' : 'Histórico de envios ao RH' }}</h2>
                    <p>{{ $panel === 'hr-queue' ? 'Você pode cancelar um envio enquanto ele ainda aguarda na fila.' : 'Cada tentativa de envio fica registrada, inclusive falhas e cancelamentos.' }}</p>
                </div>
                @if($panel === 'hr-queue')
                    <a href="{{ route('admin.contatos.index', ['view' => 'hr-queue']) }}" class="email-btn email-btn--ghost"><i class="fa-solid fa-rotate"></i> Atualizar fila</a>
                @endif
            </div>
            @if($hrAttempts->isEmpty())
                <div class="sax-cat__empty">{{ $panel === 'hr-queue' ? 'Nenhum currículo aguardando ou em processamento.' : 'Nenhum envio de currículo registrado ainda.' }}</div>
            @else
                <div class="email-history-wrap">
                    <table class="email-history-table hr-attempts-table">
                        <thead><tr><th>Currículo</th><th>Loja / destino</th><th>Status</th><th>Entrada na fila</th><th>Início / fim</th><th>Origem</th><th>Ação</th></tr></thead>
                        <tbody>
                        @foreach($hrAttempts as $attempt)
                            <tr>
                                <td><strong>{{ $attempt->candidate_name }}</strong><br><small>{{ $attempt->candidate_email }}</small><br><small>Registro #{{ $attempt->contact_id }}</small></td>
                                <td>{{ $attempt->store_name ?: 'Não informada' }}<br><small>{{ $attempt->destination ?: 'Destino indisponível' }}</small></td>
                                <td>
                                    <span class="hr-attempt-status hr-attempt-status--{{ $attempt->status }}">{{ ['queued' => 'Aguardando', 'processing' => 'Processando', 'sent' => 'Enviado', 'failed' => 'Falha', 'canceled' => 'Cancelado'][$attempt->status] ?? $attempt->status }}</span>
                                    @if($attempt->error)<br><small class="hr-attempt-error">{{ $attempt->error }}</small>@endif
                                </td>
                                <td>{{ $attempt->created_at->format('d/m/Y H:i:s') }}</td>
                                <td>
                                    @if($attempt->started_at)Início: {{ $attempt->started_at->format('d/m/Y H:i:s') }}<br>@endif
                                    @if($attempt->finished_at)Fim: {{ $attempt->finished_at->format('d/m/Y H:i:s') }}@else—@endif
                                </td>
                                <td>{{ ['automatic' => 'Automático', 'manual' => 'Manual', 'legacy' => 'Registro anterior'][$attempt->source] ?? $attempt->source }}@if($attempt->initiated_by)<br><small>{{ $attempt->initiator?->name ?: 'Usuário #'.$attempt->initiated_by }}</small>@endif
                                    @if($attempt->canceled_by)<br><small>Cancelado por {{ $attempt->canceler?->name ?: 'usuário #'.$attempt->canceled_by }}</small>@endif
                                </td>
                                <td>
                                    @if($attempt->status === 'queued')
                                        <form method="POST" action="{{ route('admin.contacts.hr-cancel', $attempt) }}" data-confirm="Retirar este currículo da fila de envio ao RH?">
                                            @csrf
                                            <button type="submit" class="email-btn email-btn--ghost">Parar envio</button>
                                        </form>
                                    @elseif($attempt->status === 'processing')
                                        <small>Envio já iniciado</small>
                                    @else
                                        <small>—</small>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="sax-cat__pag">{{ $hrAttempts->appends(['view' => $panel])->links() }}</div>
            @endif
        @elseif($panel === 'templates')
            @if($emailTemplates->isEmpty())
                <div class="sax-cat__empty">
                    <i class="fa-solid fa-layer-group d-block mb-2"></i>
                    Nenhum template salvo. Crie seu primeiro modelo reutilizável.
                </div>
            @else
                <div class="email-template-grid">
                    @foreach($emailTemplates as $template)
                        <article class="email-template-card">
                            <h3>{{ $template->name }}</h3>
                            <p><strong>Assunto:</strong> {{ Str::limit($template->subject, 85) }}</p>
                            <p>Atualizado em {{ $template->updated_at->format('d/m/Y H:i') }}@if($template->creator) por {{ $template->creator->name }}@endif</p>
                            <div class="email-template-card__actions">
                                <a class="email-btn email-btn--ghost" href="{{ route('admin.email-templates.edit', $template) }}"><i class="fa-solid fa-pen"></i> Editar</a>
                                <a class="email-btn email-btn--primary" href="{{ route('admin.emails.create', ['template' => $template->id]) }}"><i class="fa-solid fa-paper-plane"></i> Usar</a>
                                <form method="POST" action="{{ route('admin.email-templates.destroy', $template) }}" data-confirm="Remover este template?">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="email-btn email-btn--ghost" title="Excluir"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="sax-cat__pag">{{ $emailTemplates->appends(['view' => 'templates'])->links() }}</div>
            @endif
        @else
            @if($campaigns->isEmpty())
                <div class="sax-cat__empty">Nenhum envio realizado ainda.</div>
            @else
                <div class="email-history-wrap">
                    <table class="email-history-table">
                        <thead><tr><th>Data</th><th>Assunto</th><th>Público</th><th>Status</th><th>Destinatários</th><th>Enviados</th><th>Falhas</th><th>Criado por</th></tr></thead>
                        <tbody>
                        @foreach($campaigns as $campaign)
                            <tr>
                                <td>{{ $campaign->created_at->format('d/m/Y H:i') }}</td>
                                <td><strong>{{ Str::limit($campaign->subject, 70) }}</strong>@if($campaign->type === 'reply')<br><small>Resposta individual</small>@endif</td>
                                <td>{{ \App\Services\EmailAudienceService::AUDIENCES[$campaign->audience] ?? $campaign->audience }}</td>
                                <td><span class="email-status email-status--{{ $campaign->status }}">{{ ['pending'=>'Aguardando','processing'=>'Enviando','completed'=>'Concluído'][$campaign->status] ?? $campaign->status }}</span></td>
                                <td>{{ $campaign->recipient_count }}</td>
                                <td>{{ $campaign->sent_count }}</td>
                                <td>{{ $campaign->failed_count }}</td>
                                <td>{{ $campaign->creator?->name ?: 'Usuário removido' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="sax-cat__pag">{{ $campaigns->appends(['view' => 'history'])->links() }}</div>
            @endif
        @endif
    </div>
</x-admin.card>
@endsection
