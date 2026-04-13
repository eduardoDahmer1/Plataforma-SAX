@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="{{ __('messages.diretorio_usuarios') }}"
        description="{!! __('messages.registros_ativos_total', ['total' => $userCount]) !!}"
        actionUrl="{{ route('admin.users.create') }}"
        actionLabel="{{ __('messages.novo_usuario_btn') }}" />

    <x-admin.alert />

    {{-- Filtros e Busca --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-3 border-bottom gap-3">
        <div class="d-flex align-items-center gap-3">
            <label class="x-small fw-bold text-uppercase text-secondary tracking-tighter">{{ __('messages.filtrar_tipo_label') }}</label>
            <select id="filterUserType" class="form-select form-select-sm border-0 bg-light-subtle rounded-0 px-3" style="width: 180px;">
                <option value="all">{{ __('messages.todos_os_niveis') }}</option>
                <option value="1">{{ __('messages.admin_master') }}</option>
                <option value="2">{{ __('messages.usuario_comum') }}</option>
                <option value="3">{{ __('messages.usuario_curso') }}</option>
            </select>
        </div>
    </div>

    {{-- Tabela de Usuários Estilo Diretório --}}
    <div class="table-responsive">
        <table class="table align-middle border-top-0" id="usersTable">
            <thead class="bg-white">
                <tr class="text-uppercase x-small tracking-wider text-secondary">
                    <th class="py-3 border-0 fw-bold" style="width: 60px;">{{ __('messages.col_id_user') }}</th>
                    <th class="py-3 border-0 fw-bold">{{ __('messages.col_usuario') }}</th>
                    <th class="py-3 border-0 fw-bold">{{ __('messages.col_nivel_acesso') }}</th>
                    <th class="py-3 border-0 fw-bold">{{ __('messages.col_registro') }}</th>
                    <th class="py-3 border-0 fw-bold text-end">{{ __('messages.col_acoes_user') }}</th>
                </tr>
            </thead>
            <tbody class="border-top-0">
                @forelse($users as $user)
                <tr class="border-bottom clickable-row" data-usertype="{{ $user->user_type }}">
                    <td class="py-4 text-secondary small">#{{ $user->id }}</td>
                    <td class="py-4">
                        <div class="d-flex align-items-center">
                            <div class="user-initial me-3">{{ substr($user->name, 0, 1) }}</div>
                            <div>
                                <span class="d-block fw-bold text-dark">{{ $user->name }}</span>
                                <span class="x-small text-muted">{{ $user->email }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="py-4">
                        <form action="{{ route('admin.users.updateType', $user->id) }}" method="POST" class="d-flex align-items-center gap-2">
                            @csrf
                            <select name="user_type" class="form-select form-select-sm border-0 bg-light-subtle x-small fw-bold rounded-0 py-1" onchange="this.form.submit()">
                                <option value="1" {{ $user->user_type == 1 ? 'selected' : '' }}>{{ __('messages.admin_master') }}</option>
                                <option value="2" {{ $user->user_type == 2 ? 'selected' : '' }}>{{ __('messages.usuario_comum') }}</option>
                                <option value="3" {{ $user->user_type == 3 ? 'selected' : '' }}>{{ __('messages.usuario_curso') }}</option>
                            </select>
                            <span class="status-dot type-{{ $user->user_type }}"></span>
                        </form>
                    </td>
                    <td class="py-4 text-secondary small">
                        {{ $user->created_at->format('d/m/Y') }}
                    </td>
                    <td class="py-4 text-end">
                        <div class="d-flex justify-content-end gap-3">
                            <a href="{{ route('admin.clients.show', $user->id) }}" class="text-dark text-decoration-none x-small fw-bold tracking-tighter hover-underline">
                                {{ __('messages.detalhes_btn') }}
                            </a>
                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('{{ __('messages.confirmar_eliminar_usuario') }}')" class="m-0">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-clean text-danger x-small fw-bold tracking-tighter">
                                    {{ __('messages.eliminar_user_btn') }}
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted small italic">{{ __('messages.nenhum_usuario_encontrado') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5 d-flex justify-content-center">
        {{ $users->links() }}
    </div>
</x-admin.card>
@endsection