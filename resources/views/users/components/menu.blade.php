<div class="list-group mb-4">

    {{-- 🔑 Informações Pessoais --}}
    <div class="list-group-item bg-light fw-bold">
        <i class="fa fa-id-card me-2"></i> Informações Pessoais
    </div>
    <a href="{{ route('user.profile.edit') }}" class="list-group-item list-group-item-action">
        <i class="fa fa-user-edit me-2"></i> Editar Dados Pessoais
    </a>
    <a class="list-group-item list-group-item-action">
        <i class="fa fa-lock me-2"></i> Segurança / Alterar Senha
    </a>
    <!-- <a class="list-group-item list-group-item-action">
        <i class="fa fa-credit-card me-2"></i> Métodos de Pagamento
    </a> -->

    {{-- 🛒 Pedidos e Compras --}}
    <div class="list-group-item bg-light fw-bold mt-3">
        <i class="fa fa-shopping-bag me-2"></i> Pedidos e Compras
    </div>
    <a class="list-group-item list-group-item-action">
        <i class="fa fa-list me-2"></i> Histórico de Pedidos
    </a>
    <a class="list-group-item list-group-item-action">
        <i class="fa fa-file-invoice me-2"></i> Notas Fiscais / Comprovantes
    </a>

    {{-- 💳 Pagamentos e Assinaturas --}}
    <div class="list-group-item bg-light fw-bold mt-3">
        <i class="fa fa-money-bill me-2"></i> Pagamentos e Assinaturas
    </div>
    <a class="list-group-item list-group-item-action">
        <i class="fa fa-history me-2"></i> Histórico de Pagamentos
    </a>

    {{-- ❤️ Favoritos --}}
    <div class="list-group-item bg-light fw-bold mt-3">
        <i class="fa fa-heart me-2"></i> Favoritos
    </div>
    <a class="list-group-item list-group-item-action">
        <i class="fa fa-star me-2"></i> Lista de Desejos
    </a>

    {{-- 🎁 Cupons e Benefícios --}}
    <div class="list-group-item bg-light fw-bold mt-3">
        <i class="fa fa-gift me-2"></i> Cupons e Benefícios
    </div>
    <a class="list-group-item list-group-item-action">
        <i class="fa fa-ticket-alt me-2"></i> Meus Cupons
    </a>

    {{-- 📞 Suporte --}}
    <div class="list-group-item bg-light fw-bold mt-3">
        <i class="fa fa-headset me-2"></i> Suporte
    </div>
    <a class="list-group-item list-group-item-action">
        <i class="fa fa-envelope me-2"></i> Meus Chamados
    </a>
    <a class="list-group-item list-group-item-action">
        <i class="fa fa-question-circle me-2"></i> Central de Ajuda / FAQ
    </a>

    {{-- 📄 Outros --}}
    <div class="list-group-item bg-light fw-bold mt-3">
        <i class="fa fa-cog me-2"></i> Outros
    </div>
    <a class="list-group-item list-group-item-action">
        <i class="fa fa-sliders-h me-2"></i> Preferências
    </a>
    <a class="list-group-item list-group-item-action">
        <i class="fa fa-download me-2"></i> Baixar meus Dados
    </a>
    <a class="list-group-item list-group-item-action text-danger">
        <i class="fa fa-trash-alt me-2"></i> Excluir Conta
    </a>

    {{-- 🚪 Logout --}}
    <a href="{{ route('logout') }}"
       onclick="event.preventDefault();document.getElementById('logout-form').submit();"
       class="list-group-item list-group-item-action text-danger mt-3">
       <i class="fa fa-sign-out-alt me-2"></i> Sair
    </a>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>
</div>
