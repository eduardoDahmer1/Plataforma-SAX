@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="{{ __('messages.diretorio_usuarios') }}"
        description="{!! __('messages.registros_ativos_total', ['total' => $users->total()]) !!}"
        actionUrl="{{ route('admin.users.create') }}"
        actionLabel="{{ __('messages.novo_usuario_btn') }}" />

    <x-admin.alert />

    <div class="mb-4">
        <form method="GET" action="{{ route('admin.clients.index') }}" id="filterForm" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="x-small fw-bold text-uppercase">Tipo</label>
                <select name="user_type" class="form-select rounded-0" onchange="applyFilters()">
                    <option value="">{{ __('messages.todos_os_niveis') }}</option>
                    <option value="1" @selected(request('user_type') == '1')>{{ __('messages.admin_master') }}</option>
                    <option value="2" @selected(request('user_type') == '2')>{{ __('messages.usuario_comum') }}</option>
                    <option value="3" @selected(request('user_type') == '3')>{{ __('messages.usuario_curso') }}</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="x-small fw-bold text-uppercase">Buscar</label>
                <input type="text" name="search" class="form-control rounded-0" placeholder="Nome ou email..." value="{{ request('search') }}" onchange="applyFilters()">
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
        </form>
    </div>

    <div class="row g-3">
        @forelse($users as $user)
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 rounded-0 shadow-sm border-0 border-start border-4" style="border-left-color: #212529 !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3 bg-light d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; border-radius: 50%; font-weight: bold; background: #eee;">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                        <div>
                            <span class="d-block fw-bold text-dark">{{ $user->name }}</span>
                            <span class="small text-muted">{{ $user->email }}</span>
                        </div>
                    </div>
                    <div class="small border-top pt-2">
                        <div class="d-flex justify-content-between mb-1">
                            <span>ID:</span> <span class="fw-bold">#{{ $user->id }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Registro:</span> <span>{{ $user->created_at->format('d/m/Y') }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Tipo:</span> 
                            <span class="badge bg-light text-dark border">
                                @if($user->user_type == 1) Master
                                @elseif($user->user_type == 2) Comum
                                @else Curso @endif
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 p-3">
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.clients.show', $user->id) }}" class="btn btn-dark btn-sm flex-grow-1 rounded-0">Detalhes</a>
                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Confirma exclusão?')" class="m-0">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-0"><i class="fa fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5 text-muted">Nenhum usuário encontrado.</div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $users->appends(request()->query())->links() }}
    </div>
</x-admin.card>

<script>
function applyFilters() {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    window.location.href = window.location.pathname + '?' + params.toString();
}
</script>
@endsection