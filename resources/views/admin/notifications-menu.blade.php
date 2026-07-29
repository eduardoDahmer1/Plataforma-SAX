<div class="sax-admin-notifications">
    <button
        class="sax-admin-notifications__trigger"
        type="button"
        id="adminNotificationsButton"
        aria-controls="adminNotificationsDrawer"
        aria-expanded="false"
        aria-label="Abrir notificações administrativas"
    >
        <i class="fa-regular fa-bell" aria-hidden="true"></i>

        @if ($adminUnreadNotificationsCount > 0)
            <span class="sax-admin-notifications__badge" data-notifications-badge>
                {{ $adminUnreadNotificationsCount > 99 ? '99+' : $adminUnreadNotificationsCount }}
            </span>
        @endif
    </button>

    <div class="sax-admin-notifications__overlay" id="adminNotificationsOverlay"></div>
    <aside
        class="sax-admin-notifications__drawer"
        id="adminNotificationsDrawer"
        role="dialog"
        aria-modal="true"
        aria-labelledby="adminNotificationsTitle"
        aria-hidden="true"
    >
        @include('admin.notifications-content')
    </aside>
</div>
