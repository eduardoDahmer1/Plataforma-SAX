@extends('layout.admin')

@section('content')
<div class="container-fluid py-4 px-md-5">
    
    {{-- Header Minimalista --}}
    <div class="d-flex justify-content-between align-items-end mb-5">
        <div>
            <h1 class="h4 fw-light text-uppercase tracking-wider mb-1">Directorio de Usuarios</h1>
            <p class="small text-secondary mb-0">Total: <span class="text-dark fw-bold">{{ $userCount }}</span> registros activos</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-dark btn-sm rounded-0 px-4 text-uppercase fw-bold tracking-wider">
            Nuevo Usuario
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-dark border-0 rounded-0 small mb-4 py-3 shadow-sm d-flex justify-content-between align-items-center" role="alert">
            <span><i class="fa fa-check me-2"></i> {{ session('success') }}</span>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filtros e Busca --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-3 border-bottom gap-3">
        <div class="d-flex align-items-center gap-3">
            <label class="x-small fw-bold text-uppercase text-secondary tracking-tighter">Filtrar por tipo:</label>
            <select id="filterUserType" class="form-select form-select-sm border-0 bg-light-subtle rounded-0 px-3" style="width: 180px;">
                <option value="all">Todos los niveles</option>
                <option value="1">Admin Master</option>
                <option value="2">Usuario Común</option>
                <option value="3">Usuario Curso</option>
            </select>
        </div>
    </div>

    {{-- Tabela de Usuários Estilo Diretório --}}
    <div class="table-responsive">
        <table class="table align-middle border-top-0" id="usersTable">
            <thead class="bg-white">
                <tr class="text-uppercase x-small tracking-wider text-secondary">
                    <th class="py-3 border-0 fw-bold" style="width: 60px;">ID</th>
                    <th class="py-3 border-0 fw-bold">Usuario</th>
                    <th class="py-3 border-0 fw-bold">Nivel de Acceso</th>
                    <th class="py-3 border-0 fw-bold">Registro</th>
                    <th class="py-3 border-0 fw-bold text-end">Acciones</th>
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
                                <option value="1" {{ $user->user_type == 1 ? 'selected' : '' }}>ADMIN</option>
                                <option value="2" {{ $user->user_type == 2 ? 'selected' : '' }}>USUARIO</option>
                                <option value="3" {{ $user->user_type == 3 ? 'selected' : '' }}>CURSO</option>
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
                                DETALLES
                            </a>
                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('¿Eliminar usuario?')" class="m-0">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-clean text-danger x-small fw-bold tracking-tighter">
                                    ELIMINAR
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted small italic">No se encontraron usuarios en la base de datos.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5 d-flex justify-content-center">
        {{ $users->links() }}
    </div>
</div>

<style>
    /* Minimalist UI Components */
    .tracking-wider { letter-spacing: 0.12em; }
    .tracking-tighter { letter-spacing: 0.05em; }
    .x-small { font-size: 0.65rem; }
    .bg-light-subtle { background-color: #f8f9fa !important; }
    
    /* User Avatar Initial */
    .user-initial {
        width: 35px; height: 35px;
        background: #000; color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-weight: 900; font-size: 0.8rem;
    }

    /* Status Dots por tipo */
    .status-dot { height: 6px; width: 6px; border-radius: 50%; display: inline-block; }
    .status-dot.type-1 { background: #10b981; } /* Admin */
    .status-dot.type-2 { background: #3b82f6; } /* Usuario */
    .status-dot.type-3 { background: #f59e0b; } /* Curso */

    /* Buttons and Links */
    .btn-clean { background: none; border: none; padding: 0; cursor: pointer; }
    .hover-underline:hover { text-decoration: underline !important; }
    
    .table td { border-bottom: 1px solid #f1f1f1; transition: 0.2s; }
    .clickable-row:hover td { background-color: #fafafa; }

    /* Custom Select */
    .form-select-sm { cursor: pointer; }
</style>

<script>
document.getElementById('filterUserType').addEventListener('change', function() {
    const filter = this.value;
    const rows = document.querySelectorAll('#usersTable tbody tr[data-usertype]');
    rows.forEach(row => {
        row.style.display = (filter === 'all' || row.getAttribute('data-usertype') === filter) ? '' : 'none';
    });
});
</script>
@endsection