@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="{{ __('messages.diretorio_usuarios') }}"
        description="{!! __('messages.registros_ativos_total', ['total' => $users->total()]) !!}"
        actionUrl="{{ route('admin.users.create') }}"
        actionLabel="{{ __('messages.novo_usuario_btn') }}" />

    <x-admin.alert />

    <form id="bulkDeleteUsersForm" method="POST" action="{{ route('admin.users.bulk-destroy') }}">
        @csrf
        @method('DELETE')
    </form>
    @error('user_ids')<div class="alert alert-danger">{{ $message }}</div>@enderror

    <div class="mb-4">
        <form method="GET" action="{{ route('admin.clients.index') }}" id="filterForm" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="x-small fw-bold text-uppercase">Tipo</label>
                <select name="user_type" class="form-select rounded-0" onchange="applyFilters()">
                    <option value="">{{ __('messages.todos_os_niveis') }}</option>
                    <option value="1" @selected(request('user_type') == '1')>{{ __('messages.admin_master') }}</option>
                    <option value="4" @selected(request('user_type') == '4')>{{ __('messages.admin_editor') }}</option>
                    <option value="2" @selected(request('user_type') == '2')>{{ __('messages.usuario_comum') }}</option>
                    <option value="3" @selected(request('user_type') == '3')>{{ __('messages.usuario_curso') }}</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="x-small fw-bold text-uppercase">Buscar</label>
                <input type="text" name="search" class="form-control rounded-0" placeholder="Nome ou email..." value="{{ request('search') }}" onchange="applyFilters()">
            </div>
            <div class="col-md-2">
                <label class="x-small fw-bold text-uppercase">Verificação</label>
                <select name="account_filter" class="form-select rounded-0" onchange="applyFilters()">
                    <option value="">Todas as contas</option>
                    <option value="suspicious" @selected(request('account_filter') === 'suspicious')>Possíveis contas falsas</option>
                    <option value="unverified" @selected(request('account_filter') === 'unverified')>E-mail não verificado</option>
                    <option value="verified" @selected(request('account_filter') === 'verified')>E-mail verificado</option>
                    <option value="without_orders" @selected(request('account_filter') === 'without_orders')>Sem pedidos</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="x-small fw-bold text-uppercase">Exibir</label>
                <select name="per_page" class="form-select rounded-0" onchange="applyFilters()">
                    @foreach([20, 30, 40, 100] as $opt)
                        <option value="{{ $opt }}" @selected($perPage == $opt)>{{ $opt }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.clients.index') }}" class="btn btn-outline-secondary rounded-0 w-100">Limpar</a>
            </div>
            <div class="col-md-3 ms-auto">
                <a href="{{ route('admin.clients.index', ['account_filter' => 'suspicious', 'per_page' => 100]) }}" class="btn btn-warning rounded-0 w-100">
                    <i class="fa fa-shield-halved me-1"></i> Verificar contas suspeitas
                </a>
            </div>
        </form>
    </div>

    @if(request('account_filter') === 'suspicious')
        <div class="alert alert-warning d-flex gap-2 align-items-start">
            <i class="fa fa-triangle-exclamation mt-1"></i>
            <div><strong>Revise antes de excluir.</strong> O verificador mostra clientes recentes sem pedidos e sem e-mail confirmado, além de nomes com links ou promoções. Nenhuma conta é apagada automaticamente.</div>
        </div>
    @endif

    @if(request('account_filter') === 'suspicious')
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 border rounded-2 p-3 bg-light">
            <label class="form-check mb-0">
                <input id="selectAllUsers" class="form-check-input" type="checkbox">
                <span class="form-check-label fw-semibold">Selecionar todas as contas suspeitas elegíveis desta página</span>
            </label>
            <button id="bulkDeleteUsersButton" type="submit" form="bulkDeleteUsersForm" class="btn btn-danger" disabled>
                <i class="fa fa-trash me-1"></i> Excluir selecionados
            </button>
        </div>
    @endif

    <div class="row g-3">
        @forelse($users as $user)
        @php
            $bulkEligible = (int) $user->user_type === \App\Models\User::TYPE_CUSTOMER
                && auth()->id() !== $user->id
                && (int) $user->orders_count === 0
                && request('account_filter') === 'suspicious';
        @endphp
        <div class="col-12 col-md-6 col-lg-4">
            <article class="card admin-client-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        @if(request('account_filter') === 'suspicious')
                            <input class="form-check-input me-3 user-bulk-checkbox" type="checkbox" name="user_ids[]" value="{{ $user->id }}"
                                form="bulkDeleteUsersForm" @disabled(! $bulkEligible)
                                title="{{ $bulkEligible ? 'Selecionar usuário' : 'Contas administrativas ou com pedidos são protegidas' }}"
                                aria-label="Selecionar {{ $user->name }}">
                        @endif
                        <div class="admin-client-card__avatar me-3">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                        <div>
                            <span class="d-block fw-bold text-dark text-break">{{ $user->name }}</span>
                            <span class="small text-muted text-break">{{ $user->email }}</span>
                        </div>
                    </div>
                    @if(request('account_filter') === 'suspicious' && $user->suspicion_reasons)
                        <div class="d-flex flex-wrap gap-1 mb-2">
                            @foreach($user->suspicion_reasons as $reason)
                                <span class="badge text-bg-warning">{{ $reason }}</span>
                            @endforeach
                        </div>
                    @endif
                    <div class="small border-top pt-2">
                        <div class="d-flex justify-content-between mb-1">
                            <span>ID:</span> <span class="fw-bold">#{{ $user->id }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Registro:</span> <span>{{ $user->created_at->format('d/m/Y') }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Tipo:</span> 
                            <span class="badge bg-light text-dark border">{{ $user->user_role }}</span>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <span>E-mail:</span>
                            <span class="badge {{ $user->email_verified_at ? 'text-bg-success' : 'text-bg-warning' }}">{{ $user->email_verified_at ? 'Verificado' : 'Não verificado' }}</span>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <span>Pedidos:</span> <span class="fw-bold">{{ $user->orders_count }}</span>
                        </div>
                        <form action="{{ route('admin.users.updateType', $user->id) }}" method="POST" class="mt-3 pt-3 border-top">
                            @csrf
                            <label class="x-small fw-bold text-uppercase mb-1" for="user-type-{{ $user->id }}">{{ __('messages.change_user_type') }}</label>
                            <div class="d-flex flex-column flex-sm-row align-items-stretch gap-2">
                                <select id="user-type-{{ $user->id }}" name="user_type" class="form-select form-select-sm rounded-2 flex-grow-1" {{ auth()->id() === $user->id ? 'disabled' : '' }}>
                                    <option value="1" @selected((int) $user->user_type === 1)>{{ __('messages.admin_master') }}</option>
                                    <option value="4" @selected((int) $user->user_type === 4)>{{ __('messages.admin_editor') }}</option>
                                    <option value="2" @selected((int) $user->user_type === 2)>{{ __('messages.usuario_comum') }}</option>
                                    <option value="3" @selected((int) $user->user_type === 3)>{{ __('messages.usuario_curso') }}</option>
                                </select>
                                <button type="submit" class="btn btn-outline-dark btn-sm rounded-2 px-3 flex-shrink-0" {{ auth()->id() === $user->id ? 'disabled' : '' }}>
                                    {{ __('messages.save_btn') }}
                                </button>
                            </div>
                            @if(auth()->id() === $user->id)
                                <small class="text-muted">{{ __('messages.own_master_role_locked') }}</small>
                            @endif
                        </form>
                    </div>
                </div>
                <div class="card-footer admin-client-card__footer">
                    <div class="d-flex align-items-stretch gap-3">
                        <a href="{{ route('admin.clients.show', $user->id) }}" class="btn btn-dark btn-sm flex-grow-1 d-flex align-items-center justify-content-center">Detalhes</a>
                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Confirma exclusão?')" class="m-0">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline-dark btn-sm h-100 px-3" title="Excluir usuário" aria-label="Excluir usuário" @disabled(auth()->id() === $user->id || (int) $user->orders_count > 0)><i class="fa fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            </article>
        </div>
        @empty
        <div class="col-12 text-center py-5 text-muted">Nenhum usuário encontrado.</div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $users->appends(request()->query())->links() }}
    </div>
</x-admin.card>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('bulkDeleteUsersForm');
    const selectAll = document.getElementById('selectAllUsers');
    const submit = document.getElementById('bulkDeleteUsersButton');
    const checkboxes = Array.from(document.querySelectorAll('.user-bulk-checkbox:not(:disabled)'));
    if (!form || !selectAll || !submit) return;

    function refreshSelection() {
        const selected = checkboxes.filter(function (checkbox) { return checkbox.checked; }).length;
        submit.disabled = selected === 0;
        submit.innerHTML = '<i class="fa fa-trash me-1"></i> ' + (selected ? 'Excluir ' + selected + (selected === 1 ? ' usuário' : ' usuários') : 'Excluir selecionados');
        selectAll.checked = selected > 0 && selected === checkboxes.length;
        selectAll.indeterminate = selected > 0 && selected < checkboxes.length;
        selectAll.disabled = checkboxes.length === 0;
    }

    selectAll.addEventListener('change', function () {
        checkboxes.forEach(function (checkbox) { checkbox.checked = selectAll.checked; });
        refreshSelection();
    });
    checkboxes.forEach(function (checkbox) { checkbox.addEventListener('change', refreshSelection); });
    form.addEventListener('submit', function (event) {
        const selected = checkboxes.filter(function (checkbox) { return checkbox.checked; }).length;
        if (!selected || !confirm('Excluir permanentemente ' + selected + (selected === 1 ? ' usuário selecionado?' : ' usuários selecionados?') + ' Contas com pedidos serão preservadas.')) {
            event.preventDefault();
        }
    });
    refreshSelection();
});
</script>
@endpush
