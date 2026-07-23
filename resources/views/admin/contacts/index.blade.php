@extends('layout.admin')

@section('content')
@php
    $tipos = [
        1 => ['rotulo' => __('messages.contato_tipo_consulta'), 'cor' => '#3b6fd4', 'icone' => 'fa-comment'],
        2 => ['rotulo' => __('messages.contato_tipo_curriculo'), 'cor' => '#1f7a37', 'icone' => 'fa-file-lines'],
        3 => ['rotulo' => __('messages.contato_tipo_newsletter'), 'cor' => '#9a7b1f', 'icone' => 'fa-envelope'],
    ];
@endphp

<x-admin.card>
    <div class="sax-cat sax-msg" id="msg-app" data-delete-url="{{ url('admin/contatos') }}" data-confirm-delete="{{ __('messages.confirmar_exclusao') }}" data-error-message="{{ __('messages.activate_erro') }}">

        {{-- Cabeçalho --}}
        <div class="sax-cat__top">
            <div>
                <h1 class="sax-cat__title">{{ __('messages.centro_mensagens_titulo') }}</h1>
                <span class="sax-cat__sub">{{ __('messages.gestao_comunicacoes_desc') }}</span>
            </div>

            <a href="{{ route('admin.contacts.export', ['type' => $type, 'period' => $period]) }}" class="sax-cat__new">
                <i class="fas fa-download"></i> {{ __('messages.exportar_btn') }}
            </a>
        </div>

        <div class="contacts-period-panel">
            <div>
                <span class="contacts-period-eyebrow">Resumo dos contatos</span>
                <strong>{{ $periodLabel }}</strong>
            </div>
            <div class="contacts-period-actions">
                @foreach ($periods as $periodKey => $periodName)
                    <a href="{{ route('admin.contatos.index', ['period' => $periodKey, 'type' => $type, 'search' => $search, 'per_page' => $perPage]) }}"
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
                @if ($type)
                    <input type="hidden" name="type" value="{{ $type }}">
                @endif
                <input type="hidden" name="per_page" value="{{ $perPage }}">
            </form>

            <div class="sax-msg__tabs">
                <a href="{{ route('admin.contatos.index', ['period' => $period, 'search' => $search, 'per_page' => $perPage]) }}"
                   class="sax-chip {{ !$type ? 'is-on' : '' }}">
                    {{ __('messages.cupon_situacao_todas') }} <span>{{ $totais['all'] }}</span>
                </a>

                @foreach ($tipos as $id => $info)
                    <a href="{{ route('admin.contatos.index', ['period' => $period, 'type' => $id, 'search' => $search, 'per_page' => $perPage]) }}"
                       class="sax-chip {{ (int) $type === $id ? 'is-on' : '' }}">
                        {{ $info['rotulo'] }} <span>{{ $totais[$id] }}</span>
                    </a>
                @endforeach
            </div>

            <form method="GET" action="{{ route('admin.contatos.index') }}" class="sax-msg__per">
                <input type="hidden" name="period" value="{{ $period }}">
                @if ($type) <input type="hidden" name="type" value="{{ $type }}"> @endif
                @if ($search) <input type="hidden" name="search" value="{{ $search }}"> @endif
                <select name="per_page" onchange="this.form.submit()">
                    @foreach ([20, 30, 50, 100] as $opt)
                        <option value="{{ $opt }}" @selected($perPage == $opt)>{{ $opt }}</option>
                    @endforeach
                </select>
            </form>

            <span class="sax-msg__hint" id="msg-feedback" role="status"></span>
        </div>

        {{-- Lista --}}
        <div class="sax-msg__list">
            @forelse ($contacts as $contact)
                @php
                    $info = $tipos[$contact->contact_type] ?? ['rotulo' => '—', 'cor' => '#999', 'icone' => 'fa-circle'];
                    $anexoUrl = $contact->attachment ? asset('storage/' . $contact->attachment) : null;
                    $extensao = $contact->attachment ? strtoupper(pathinfo($contact->attachment, PATHINFO_EXTENSION)) : null;
                @endphp

                <article class="sax-msg__item" data-row data-id="{{ $contact->id }}">
                    <div class="sax-msg__main" data-toggle role="button" tabindex="0"
                         aria-expanded="false" title="{{ __('messages.contato_ver_completo') }}">

                        <span class="sax-msg__type" style="--cor: {{ $info['cor'] }}" title="{{ $info['rotulo'] }}">
                            <i class="fa-solid {{ $info['icone'] }}"></i>
                        </span>

                        <div class="sax-msg__who">
                            <span class="sax-msg__name">{{ $contact->name ?: '—' }}</span>
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
                            <span class="sax-msg__label">{{ __('messages.contato_anexo') }}</span>
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
                            @if ($contact->email)
                                <a href="mailto:{{ $contact->email }}" class="sax-cat-act">
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
    </div>
</x-admin.card>
@endsection
