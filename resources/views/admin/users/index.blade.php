@extends('layout.admin')

@section('content')
<div class="container">
    <h2 class="mb-3">Gerenciar Usuários</h2>

    <p>Total de usuários cadastrados: <strong>{{ $userCount }}</strong></p>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Email</th>
                <th>Tipo de Usuário</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        @if($user->user_type == 1)
                            <span class="badge bg-success">Admin Master</span>
                        @else
                            <span class="badge bg-secondary">Usuário Comum</span>
                        @endif
                    </td>
                    <td>
                        <!-- Formulário de Atualização do Tipo de Usuário -->
                        <form action="{{ route('admin.users.updateType', $user->id) }}" method="POST" class="d-inline-flex align-items-center gap-2">
                            @csrf
                            <select name="user_type" class="form-select form-select-sm w-auto">
                                <option value="1" {{ $user->user_type == 1 ? 'selected' : '' }}>Admin</option>
                                <option value="2" {{ $user->user_type == 2 ? 'selected' : '' }}>Usuário</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary">Salvar</button>
                        </form>

                        <!-- Formulário de Exclusão do Usuário -->
                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline-flex align-items-center gap-2">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este usuário?')">Excluir</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
