<div class="list-group mb-4">
    <a href="{{ route('user.dashboard') }}" class="list-group-item list-group-item-action">
        <i class="fa fa-home me-2"></i> Dashboard
    </a>
    <a href="{{ route('user.profile.update') }}" class="list-group-item list-group-item-action">
        <i class="fa fa-user-edit me-2"></i> Atualizar Perfil
    </a>
    
    <a href="{{ route('logout') }}"
       onclick="event.preventDefault();document.getElementById('logout-form').submit();"
       class="list-group-item list-group-item-action text-danger">
       <i class="fa fa-sign-out-alt me-2"></i> Sair
    </a>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>
</div>
