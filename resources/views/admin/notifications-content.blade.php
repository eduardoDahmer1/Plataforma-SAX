@php
    $notificationStyles = [
        'new_order' => ['icon' => 'fa-receipt', 'class' => 'is-order'],
        'order_processing' => ['icon' => 'fa-gears', 'class' => 'is-order'],
        'order_shipped' => ['icon' => 'fa-truck', 'class' => 'is-order'],
        'order_completed' => ['icon' => 'fa-circle-check', 'class' => 'is-order'],
        'order_canceled' => ['icon' => 'fa-ban', 'class' => 'is-default'],
        'deposit_receipt' => ['icon' => 'fa-file-invoice-dollar', 'class' => 'is-order'],
        'payment_paid' => ['icon' => 'fa-circle-check', 'class' => 'is-order'],
        'payment_failed' => ['icon' => 'fa-triangle-exclamation', 'class' => 'is-default'],
        'payment_refunded' => ['icon' => 'fa-rotate-left', 'class' => 'is-default'],
        'checkout_error' => ['icon' => 'fa-cart-shopping', 'class' => 'is-default'],
        'high_value_abandoned_cart' => ['icon' => 'fa-cart-shopping', 'class' => 'is-order'],
        'abandoned_cart_feedback' => ['icon' => 'fa-comment-dots', 'class' => 'is-contact'],
        'low_stock' => ['icon' => 'fa-box-open', 'class' => 'is-resume'],
        'out_of_stock' => ['icon' => 'fa-box', 'class' => 'is-default'],
        'new_contact' => ['icon' => 'fa-envelope', 'class' => 'is-contact'],
        'new_resume' => ['icon' => 'fa-file-lines', 'class' => 'is-resume'],
        'new_user' => ['icon' => 'fa-user-plus', 'class' => 'is-user'],
    ];

    $notificationCategories = [
        'new_order' => 'orders',
        'order_processing' => 'orders',
        'order_shipped' => 'orders',
        'order_completed' => 'orders',
        'order_canceled' => 'orders',
        'deposit_receipt' => 'payments',
        'payment_paid' => 'payments',
        'payment_failed' => 'payments',
        'payment_refunded' => 'payments',
        'checkout_error' => 'checkout',
        'high_value_abandoned_cart' => 'checkout',
        'abandoned_cart_feedback' => 'checkout',
        'new_user' => 'clients',
        'new_contact' => 'contacts',
        'new_resume' => 'contacts',
        'low_stock' => 'catalog',
        'out_of_stock' => 'catalog',
    ];
@endphp

<div class="sax-admin-notifications__header">
    <div>
        <strong id="adminNotificationsTitle">Notificações</strong>
        <span>Últimas movimentações do painel</span>
    </div>

    <div class="sax-admin-notifications__header-actions">
        @if ($adminUnreadNotificationsCount > 0)
            <span class="sax-admin-notifications__unread" data-notifications-unread-count>
                {{ $adminUnreadNotificationsCount }} {{ $adminUnreadNotificationsCount === 1 ? 'não lida' : 'não lidas' }}
            </span>

            <form action="{{ route('admin.notifications.read-all') }}" method="POST" data-notifications-read-all>
                @csrf
                <button type="submit" class="sax-admin-notifications__read-all">
                    Marcar todas como lidas
                </button>
            </form>
        @endif

        <button type="button" class="sax-admin-notifications__close" id="adminNotificationsClose" aria-label="Fechar notificações">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
    </div>
</div>

<div class="px-3 py-2 border-bottom">
    <label class="visually-hidden" for="adminNotificationsFilter">Filtrar notificações</label>
    <select class="form-select form-select-sm" id="adminNotificationsFilter">
        <option value="all">Todas as notificações</option>
        <option value="orders">Pedidos</option>
        <option value="payments">Pagamentos</option>
        <option value="checkout">Checkout e carrinhos</option>
        <option value="clients">Clientes</option>
        <option value="contacts">Contatos e currículos</option>
        <option value="catalog">Catálogo e estoque</option>
    </select>
</div>

<div class="sax-admin-notifications__list">
    @forelse ($adminNotifications as $notification)
        @php
            $style = $notificationStyles[$notification->type]
                ?? ['icon' => 'fa-bell', 'class' => 'is-default'];
            $category = $notificationCategories[$notification->type] ?? 'other';
        @endphp

        <form action="{{ route('admin.notifications.read', $notification) }}" method="POST"
              data-notification-item data-notification-category="{{ $category }}">
            @csrf
            <button
                type="submit"
                class="sax-admin-notifications__item {{ is_null($notification->read_at) ? 'is-unread' : '' }}"
            >
                <span class="sax-admin-notifications__icon {{ $style['class'] }}">
                    <i class="fa-solid {{ $style['icon'] }}" aria-hidden="true"></i>
                </span>

                <span class="sax-admin-notifications__content">
                    <strong>{{ $notification->title }}</strong>
                    <span>{{ $notification->message }}</span>
                    <time datetime="{{ $notification->created_at?->toIso8601String() }}">
                        {{ $notification->created_at?->diffForHumans() }}
                    </time>
                </span>

                @if (is_null($notification->read_at))
                    <span class="sax-admin-notifications__dot" aria-label="Não lida"></span>
                @endif
            </button>
            @if (is_null($notification->read_at))
                <button type="button" class="sax-admin-notifications__mark-read"
                        data-notification-mark-read title="Marcar como lida">
                    <i class="fa-solid fa-check" aria-hidden="true"></i>
                    <span>Marcar como lida</span>
                </button>
            @endif
        </form>
    @empty
        <div class="sax-admin-notifications__empty">
            <i class="fa-regular fa-bell-slash" aria-hidden="true"></i>
            <strong>Nenhuma notificação</strong>
            <span>Novos pedidos e contatos aparecerão aqui.</span>
        </div>
    @endforelse

    <div class="sax-admin-notifications__empty d-none" id="adminNotificationsFilteredEmpty">
        <i class="fa-regular fa-bell-slash" aria-hidden="true"></i>
        <strong>Nenhuma notificação neste filtro</strong>
        <span>Selecione outra categoria para ver mais movimentações.</span>
    </div>
</div>
