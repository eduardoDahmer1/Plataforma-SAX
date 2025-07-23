@extends('layout.admin')

@section('content')
<div class="container">
    <h2 class="mb-3">Gerenciar Usuários</h2>
    <a href="{{ route('admin.users.create') }}" class="btn btn-success mb-3">Criar Novo Usuário</a>

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
                        <span class="badge bg-success">Usuário Comum</span>
                    @elseif($user->user_type == 2)
                        <span class="badge bg-primary">Admin Master</span>
                    @elseif($user->user_type == 3)
                        <span class="badge bg-warning text-dark">Usuário Curso</span>
                    @endif
                    </td>
                    <td>
                        <!-- Formulário de Atualização do Tipo de Usuário -->
                        <form action="{{ route('admin.users.updateType', $user->id) }}" method="POST" class="d-inline-flex align-items-center gap-2">
                            @csrf
                            <select name="user_type" class="form-select form-select-sm w-auto">
                                <option value="1" {{ $user->user_type == 1 ? 'selected' : '' }}>Admin</option>
                                <option value="2" {{ $user->user_type == 2 ? 'selected' : '' }}>Usuário</option>
                                <option value="3" {{ $user->user_type == 3 ? 'selected' : '' }}>Curso</option>
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
