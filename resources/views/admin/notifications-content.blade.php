@php
    $notificationStyles = [
        'new_order' => ['icon' => 'fa-receipt', 'class' => 'is-order'],
        'new_contact' => ['icon' => 'fa-envelope', 'class' => 'is-contact'],
        'new_resume' => ['icon' => 'fa-file-lines', 'class' => 'is-resume'],
        'new_user' => ['icon' => 'fa-user-plus', 'class' => 'is-user'],
    ];
@endphp

<div class="sax-admin-notifications__header">
    <div>
        <strong id="adminNotificationsTitle">Notificações</strong>
        <span>Últimas movimentações do painel</span>
    </div>

    <div class="sax-admin-notifications__header-actions">
        @if ($adminUnreadNotificationsCount > 0)
            <span class="sax-admin-notifications__unread">
                {{ $adminUnreadNotificationsCount }} {{ $adminUnreadNotificationsCount === 1 ? 'não lida' : 'não lidas' }}
            </span>

            <form action="{{ route('admin.notifications.read-all') }}" method="POST">
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

<div class="sax-admin-notifications__list">
    @forelse ($adminNotifications as $notification)
        @php
            $style = $notificationStyles[$notification->type]
                ?? ['icon' => 'fa-bell', 'class' => 'is-default'];
        @endphp

        <form action="{{ route('admin.notifications.read', $notification) }}" method="POST">
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
        </form>
    @empty
        <div class="sax-admin-notifications__empty">
            <i class="fa-regular fa-bell-slash" aria-hidden="true"></i>
            <strong>Nenhuma notificação</strong>
            <span>Novos pedidos e contatos aparecerão aqui.</span>
        </div>
    @endforelse
</div>
