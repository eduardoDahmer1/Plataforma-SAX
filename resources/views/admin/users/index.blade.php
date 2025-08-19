@extends('layout.admin')

@section('content')
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
        <h2 class="mb-2 mb-md-0">Gerenciar Usuários</h2>
        <a href="{{ route('admin.users.create') }}" class="btn btn-success mb-2">Criar Novo Usuário</a>
    </div>

    <p>Total de usuários cadastrados: <strong>{{ $userCount }}</strong></p>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Tipo de Usuário</th>
                            <th class="text-center">Ações</th>
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
                                    @elseif($user->user_type == 2)
                                        <span class="badge bg-primary">Usuário Comum</span>
                                    @elseif($user->user_type == 3)
                                        <span class="badge bg-warning text-dark">Usuário Curso</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-column flex-md-row align-items-center justify-content-center gap-2">
                                        <!-- Atualizar Tipo -->
                                        <form action="{{ route('admin.users.updateType', $user->id) }}" method="POST" class="d-flex gap-2 flex-wrap justify-content-center">
                                            @csrf
                                            <select name="user_type" class="form-select form-select-sm w-auto">
                                                <option value="1" {{ $user->user_type == 1 ? 'selected' : '' }}>Admin</option>
                                                <option value="2" {{ $user->user_type == 2 ? 'selected' : '' }}>Usuário</option>
                                                <option value="3" {{ $user->user_type == 3 ? 'selected' : '' }}>Curso</option>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-primary">Salvar</button>
                                        </form>

                                        <!-- Excluir -->
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Tem certeza que deseja excluir este usuário?')">
                                                Excluir
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div> <!-- /table-responsive -->
        </div>
    </div>
</div>
@endsection
