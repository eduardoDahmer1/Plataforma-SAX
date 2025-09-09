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
    <a href="{{ route('user.orders') }}" class="list-group-item list-group-item-action">
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
    <!-- Botão para abrir modal -->
    <button type="button" class="list-group-item list-group-item-action text-danger" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
        <i class="fa fa-trash-alt me-2"></i>Excluir Conta
    </button>

    <!-- Modal de confirmação -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-lg">
        <div class="modal-header bg-danger text-white">
            <h5 class="modal-title" id="confirmDeleteModalLabel"><i class="fa fa-exclamation-triangle me-2"></i>Confirmação</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
            <p>Tem certeza que deseja excluir sua conta? Esta ação não pode ser desfeita.</p>
            <form method="POST" action="{{ route('user.destroy') }}" id="deleteAccountForm">
                @csrf
                @method('DELETE')
                <div class="mb-3">
                    <input type="password" name="password" placeholder="Confirme sua senha" class="form-control" required>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Sim, excluir</button>
                </div>
            </form>
        </div>
        </div>
    </div>
    </div>

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
