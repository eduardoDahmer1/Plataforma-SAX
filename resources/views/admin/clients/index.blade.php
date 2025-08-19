@extends('layout.admin')

@section('content')
<div class="container mt-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-2">
        <h2>Clientes Cadastrados</h2>
    </div>

    <!-- Filtro rápido -->
    <div class="mb-3 d-flex flex-column flex-md-row gap-2 align-items-start">
        <label for="filterUserType" class="form-label mb-2 mb-md-0">Filtrar por tipo:</label>
        <select id="filterUserType" class="form-select" style="max-width: 200px;">
            <option value="all" selected><i class="fa fa-users me-1"></i> Todos</option>
            <option value="1"><i class="fa fa-user-shield me-1"></i> Admin</option>
            <option value="2"><i class="fa fa-user me-1"></i> Cliente</option>
            <option value="3"><i class="fa fa-graduation-cap me-1"></i> Curso</option>
        </select>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped mt-2" id="clientsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Data de Cadastro</th>
                    <th>Tipo</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clients as $client)
                <tr data-usertype="{{ $client->user_type }}">
                    <td>{{ $client->id }}</td>
                    <td>{{ $client->name }}</td>
                    <td>{{ $client->email }}</td>
                    <td>{{ $client->created_at->format('d/m/Y') }}</td>
                    <td>
                        @switch($client->user_type)
                            @case(1)
                                <i class="fa fa-user-shield me-1"></i> Admin
                            @break
                            @case(2)
                                <i class="fa fa-user me-1"></i> Cliente
                            @break
                            @case(3)
                                <i class="fa fa-graduation-cap me-1"></i> Curso
                            @break
                            @default
                                <i class="fa fa-question-circle me-1"></i> Outro
                        @endswitch
                    </td>
                    <td>
                        <a href="{{ route('admin.clients.show', $client->id) }}" class="btn btn-sm btn-info">
                            <i class="fa fa-eye me-1"></i> Detalhes
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">Nenhum cliente encontrado.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center mt-3">
        {{ $clients->links() }}
    </div>
</div>

<script>
document.getElementById('filterUserType').addEventListener('change', function() {
    const filter = this.value;
    const rows = document.querySelectorAll('#clientsTable tbody tr');

    rows.forEach(row => {
        if (filter === 'all') {
            row.style.display = '';
        } else {
            const userType = row.getAttribute('data-usertype');
            row.style.display = (userType === filter) ? '' : 'none';
        }
    });
});
</script>
@endsection
