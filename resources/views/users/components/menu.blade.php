<div class="sax-sidebar-menu mb-4">

    {{-- 🔑 Informações Pessoais --}}
    <div class="sax-menu-group">
        <span class="sax-menu-label text-uppercase letter-spacing-1">Mi Cuenta</span>
        <div class="sax-menu-items">
            <a href="{{ route('user.profile.edit') }}" class="sax-menu-link">
                <i class="fa fa-user-edit"></i> Editar Datos Personales
            </a>
            <a href="#" class="sax-menu-link">
                <i class="fa fa-lock"></i> Seguridad / Contraseña
            </a>
        </div>
    </div>

    {{-- 🛒 Pedidos e Compras --}}
    <div class="sax-menu-group mt-4">
        <span class="sax-menu-label text-uppercase letter-spacing-1">Compras</span>
        <div class="sax-menu-items">
            <a href="{{ route('user.orders') }}" class="sax-menu-link">
                <i class="fa fa-list"></i> Histórico de Pedidos
            </a>
        </div>
    </div>

    {{-- ❤️ Favoritos --}}
    <div class="sax-menu-group mt-4">
        <span class="sax-menu-label text-uppercase letter-spacing-1">Preferencia</span>
        <div class="sax-menu-items">
            <a href="{{ route('user.preferences') }}" class="sax-menu-link">
                <i class="fa fa-star"></i> Lista de Deseos
            </a>
        </div>
    </div>

    {{-- 🚪 Ações de Conta --}}
    <div class="sax-menu-group mt-5 pt-3 border-top">
        <div class="sax-menu-items">
            <a href="{{ route('logout') }}" 
               onclick="event.preventDefault();document.getElementById('logout-form').submit();"
               class="sax-menu-link text-dark fw-bold">
                <i class="fa fa-sign-out-alt"></i> Cerrar Sesión
            </a>
            
            <button type="button" class="sax-menu-link border-0 bg-transparent text-danger w-100 text-start" 
                    data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
                <i class="fa fa-trash-alt"></i> Eliminar Cuenta
            </button>
        </div>
    </div>

    {{-- Formulário de Logout --}}
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>
</div>

{{-- Modal de Confirmação Minimalista --}}
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-0">
            <div class="modal-body p-4 text-center">
                <h5 class="fw-bold text-uppercase letter-spacing-2 mb-3">¿Eliminar Cuenta?</h5>
                <p class="text-muted small px-3">Esta acción es irreversible. Todos sus datos y el historial de pedidos se perderán.</p>
                
                <form method="POST" action="{{ route('user.destroy') }}" id="deleteAccountForm">
                    @csrf @method('DELETE')
                    <div class="mb-4">
                        <input type="password" name="password" placeholder="Confirma tu contraseña" 
                               class="form-control sax-modal-input text-center" required>
                    </div>
                    <div class="d-flex flex-column gap-2">
                        <button type="submit" class="btn btn-dark rounded-0 py-2 text-uppercase fw-bold x-small">Confirmar Eliminación</button>
                        <button type="button" class="btn btn-link text-muted text-decoration-none x-small" data-bs-dismiss="modal">CANCELAR</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<style>
    /* Container do Menu */
.sax-sidebar-menu {
    background: #fff;
    padding: 20px 0;
}

/* Rótulos das Seções */
.sax-menu-label {
    display: block;
    font-size: 0.65rem;
    font-weight: 800;
    color: #999;
    padding: 0 15px 10px 15px;
    border-bottom: 1px solid #f0f0f0;
    margin-bottom: 10px;
}

/* Itens do Menu */
.sax-menu-items {
    display: flex;
    flex-direction: column;
}

.sax-menu-link {
    padding: 12px 15px;
    color: #333;
    text-decoration: none !important;
    font-size: 0.85rem;
    font-weight: 400;
    display: flex;
    align-items: center;
    transition: all 0.2s ease;
}

.sax-menu-link i {
    width: 25px;
    font-size: 0.9rem;
    color: #555;
    transition: all 0.2s ease;
}

/* Hover & Active */
.sax-menu-link:hover {
    background-color: #f8f8f8;
    color: #000;
    padding-left: 20px;
}

.sax-menu-link:hover i {
    color: #000;
}

/* Estilo do Modal */
.sax-modal-input {
    border: none;
    border-bottom: 2px solid #eee;
    border-radius: 0;
    font-size: 0.9rem;
}

.sax-modal-input:focus {
    box-shadow: none;
    border-color: #000;
}

.letter-spacing-1 { letter-spacing: 1.5px; }
.letter-spacing-2 { letter-spacing: 3px; }
.x-small { font-size: 0.7rem; }
</style>