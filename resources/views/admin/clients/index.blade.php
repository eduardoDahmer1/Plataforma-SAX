@extends('layout.admin')

@section('content')
<div class="container">
    <h2>Clientes Cadastrados</h2>

    <!-- Filtro rápido -->
    <div class="mb-3">
        <label for="filterUserType" class="form-label">Filtrar por tipo:</label>
        <select id="filterUserType" class="form-select" style="width: 200px;">
            <option value="all" selected>Todos</option>
            <option value="1">Clientes</option>
            <option value="2">Admins</option>
            <option value="3">Cursos</option>
        </select>
    </div>

    <table class="table table-bordered table-striped mt-4" id="clientsTable">
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
                            Cliente
                            @break
                        @case(2)
                            Admin
                            @break
                        @case(3)
                            Curso
                            @break
                        @default
                            Outro
                    @endswitch
                </td>
                <td>
                    <a href="{{ route('admin.clients.show', $client->id) }}" class="btn btn-sm btn-info">Detalhes</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Nenhum cliente encontrado.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="d-flex justify-content-center">
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
