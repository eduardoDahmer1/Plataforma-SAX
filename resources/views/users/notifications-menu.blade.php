<div class="sax-admin-notifications">
    <button class="sax-admin-notifications__trigger" type="button" id="adminNotificationsButton"
            aria-controls="adminNotificationsDrawer" aria-expanded="false" aria-label="{{ __('messages.notifications_open_customer') }}">
        <i class="fa-regular fa-bell" aria-hidden="true"></i>
        @if ($customerUnreadNotificationsCount > 0)
            <span class="sax-admin-notifications__badge" data-notifications-badge>{{ $customerUnreadNotificationsCount > 99 ? '99+' : $customerUnreadNotificationsCount }}</span>
        @endif
    </button>

    <div class="sax-admin-notifications__overlay" id="adminNotificationsOverlay"></div>
    <aside class="sax-admin-notifications__drawer" id="adminNotificationsDrawer" role="dialog"
           aria-modal="true" aria-labelledby="adminNotificationsTitle" aria-hidden="true"
           data-notifications-unread-singular="{{ __('messages.notifications_unread_singular') }}"
           data-notifications-unread-plural="{{ __('messages.notifications_unread_plural') }}">
        @include('users.notifications-content')
    </aside>
</div>
