@php
    $categories = [
        'customer_order_created' => 'orders', 'customer_order_processing' => 'orders',
        'customer_order_shipped' => 'orders', 'customer_order_completed' => 'orders',
        'customer_order_canceled' => 'orders', 'customer_payment_paid' => 'payments',
        'customer_payment_failed' => 'payments', 'customer_payment_refunded' => 'payments',
        'customer_password_changed' => 'security', 'customer_email_changed' => 'security',
        'customer_email_verified' => 'security', 'customer_welcome' => 'account',
        'customer_profile_updated' => 'account', 'new_category' => 'news',
        'new_brand' => 'news', 'new_coupon' => 'coupons',
    ];
    $icons = [
        'orders' => 'fa-box', 'payments' => 'fa-credit-card', 'security' => 'fa-shield-halved',
        'account' => 'fa-user', 'news' => 'fa-star', 'coupons' => 'fa-ticket',
    ];
@endphp

<div class="sax-admin-notifications__header">
    <div><strong id="adminNotificationsTitle">Minhas notificações</strong><span>Pedidos, conta e novidades SAX</span></div>
    <div class="sax-admin-notifications__header-actions">
        @if ($customerUnreadNotificationsCount > 0)
            <span class="sax-admin-notifications__unread">{{ $customerUnreadNotificationsCount }} {{ $customerUnreadNotificationsCount === 1 ? 'não lida' : 'não lidas' }}</span>
            <form action="{{ route('user.notifications.read-all') }}" method="POST">
                @csrf
                <button type="submit" class="sax-admin-notifications__read-all">Marcar todas como lidas</button>
            </form>
        @endif
        <button type="button" class="sax-admin-notifications__close" id="adminNotificationsClose" aria-label="Fechar notificações"><i class="fa-solid fa-xmark"></i></button>
    </div>
</div>

<div class="px-3 py-2 border-bottom">
    <label class="visually-hidden" for="adminNotificationsFilter">Filtrar notificações</label>
    <select class="form-select form-select-sm" id="adminNotificationsFilter">
        <option value="all">Todas as notificações</option>
        <option value="orders">Meus pedidos</option>
        <option value="payments">Pagamentos</option>
        <option value="security">Segurança</option>
        <option value="account">Minha conta</option>
        <option value="coupons">Cupons</option>
        <option value="news">Novidades</option>
    </select>
</div>

<div class="sax-admin-notifications__list">
    @forelse ($customerNotifications as $notification)
        @php($category = $categories[$notification->type] ?? 'account')
        <form action="{{ route('user.notifications.read', $notification) }}" method="POST"
              data-notification-item data-notification-category="{{ $category }}">
            @csrf
            <button type="submit" class="sax-admin-notifications__item {{ is_null($notification->read_at) ? 'is-unread' : '' }}">
                <span class="sax-admin-notifications__icon is-user"><i class="fa-solid {{ $icons[$category] ?? 'fa-bell' }}"></i></span>
                <span class="sax-admin-notifications__content">
                    <strong>{{ $notification->title }}</strong><span>{{ $notification->message }}</span>
                    <time datetime="{{ $notification->created_at?->toIso8601String() }}">{{ $notification->created_at?->diffForHumans() }}</time>
                </span>
                @if (is_null($notification->read_at))<span class="sax-admin-notifications__dot" aria-label="Não lida"></span>@endif
            </button>
        </form>
    @empty
        <div class="sax-admin-notifications__empty"><i class="fa-regular fa-bell-slash"></i><strong>Nenhuma notificação</strong><span>Suas novidades aparecerão aqui.</span></div>
    @endforelse
    <div class="sax-admin-notifications__empty d-none" id="adminNotificationsFilteredEmpty">
        <i class="fa-regular fa-bell-slash"></i><strong>Nada neste filtro</strong><span>Selecione outra categoria.</span>
    </div>
</div>
