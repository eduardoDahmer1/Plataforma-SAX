@extends('layout.admin')

@section('content')
<div class="container my-4">
    <!-- Cabeçalho -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <h2 class="fw-bold text-primary">
            <i class="fa fa-users me-2"></i> Gerenciar Usuários
        </h2>
        <a href="{{ route('admin.users.create') }}" class="btn btn-success">
            <i class="fa fa-user-plus me-1"></i> Criar Novo Usuário
        </a>
    </div>

    <!-- Info geral -->
    <p class="text-muted">Total de usuários cadastrados: 
        <span class="fw-bold">{{ $userCount }}</span>
    </p>

    <!-- Alertas -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fa fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    <!-- Filtro rápido -->
    <div class="mb-4 d-flex flex-column flex-md-row gap-2 align-items-start">
        <label for="filterUserType" class="form-label mb-0">Filtrar por tipo:</label>
        <select id="filterUserType" class="form-select" style="max-width: 220px;">
            <option value="all" selected>Todos</option>
            <option value="1">Admin</option>
            <option value="2">Usuário Comum</option>
            <option value="3">Usuário Curso</option>
        </select>
    </div>

    <!-- Grid de usuários -->
    <div class="row g-4" id="usersGrid">
        @forelse($users as $user)
            <div class="col-12 col-md-6 col-lg-4" data-usertype="{{ $user->user_type }}">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="card-title fw-bold mb-0">
                                <i class="fa fa-user-circle me-1 text-primary"></i> {{ $user->name }}
                            </h5>
                            <span class="badge 
                                @if($user->user_type == 1) bg-success
                                @elseif($user->user_type == 2) bg-primary
                                @elseif($user->user_type == 3) bg-warning text-dark
                                @else bg-secondary @endif">
                                @if($user->user_type == 1)
                                    Admin Master
                                @elseif($user->user_type == 2)
                                    Usuário Comum
                                @elseif($user->user_type == 3)
                                    Usuário Curso
                                @else
                                    Desconhecido
                                @endif
                            </span>
                        </div>

                        <p class="text-muted mb-1"><i class="fa fa-id-badge me-1"></i> ID: {{ $user->id }}</p>
                        <p class="text-muted mb-1"><i class="fa fa-envelope me-1"></i> {{ $user->email }}</p>
                        <p class="small text-muted mb-3">
                            <i class="fa fa-calendar-alt me-1"></i> Criado em {{ $user->created_at->format('d/m/Y') }}
                        </p>

                        <div class="mt-auto">
                            <!-- Ver Detalhes -->
                            <a href="{{ route('admin.clients.show', $user->id) }}" class="btn btn-sm btn-info w-100 mb-2">
                                <i class="fa fa-eye me-1"></i> Detalhes
                            </a>

                            <!-- Atualizar Tipo -->
                            <form action="{{ route('admin.users.updateType', $user->id) }}" method="POST" class="d-flex gap-2 mb-2">
                                @csrf
                                <select name="user_type" class="form-select form-select-sm w-auto">
                                    <option value="1" {{ $user->user_type == 1 ? 'selected' : '' }}>Admin</option>
                                    <option value="2" {{ $user->user_type == 2 ? 'selected' : '' }}>Usuário</option>
                                    <option value="3" {{ $user->user_type == 3 ? 'selected' : '' }}>Curso</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="fa fa-save me-1"></i> Salvar
                                </button>
                            </form>

                            <!-- Excluir -->
                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este usuário?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger w-100">
                                    <i class="fa fa-trash me-1"></i> Excluir
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info text-center shadow-sm">
                    Nenhum usuário encontrado.
                </div>
            </div>
        @endforelse
    </div>

    <!-- Paginação -->
    <div class="d-flex justify-content-center mt-4">
        {{ $users->links() }}
    </div>
</div>

<!-- Script filtro -->
<script>
document.getElementById('filterUserType').addEventListener('change', function() {
    const filter = this.value;
    const cards = document.querySelectorAll('#usersGrid [data-usertype]');
    cards.forEach(card => {
        if (filter === 'all') {
            card.style.display = '';
        } else {
            const userType = card.getAttribute('data-usertype');
            card.style.display = (userType === filter) ? '' : 'none';
        }
    });
});
</script>

<!-- CSS adicional -->
<style>
    .card {
        border-radius: 12px;
        transition: all 0.2s ease-in-out;
    }
    .card:hover {
        transform: translateY(-4px);
        box-shadow: 0px 6px 15px rgba(0,0,0,0.1);
    }
    .card-title {
        font-size: 1.1rem;
    }
</style>
@endsection
