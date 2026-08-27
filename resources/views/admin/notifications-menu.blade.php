<div class="sax-admin-notifications">
    <button
        class="sax-admin-notifications__trigger"
        type="button"
        id="adminNotificationsButton"
        aria-controls="adminNotificationsDrawer"
        aria-expanded="false"
        aria-label="{{ __('messages.notifications_open_admin') }}"
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
        data-notifications-unread-singular="{{ __('messages.notifications_unread_singular') }}"
        data-notifications-unread-plural="{{ __('messages.notifications_unread_plural') }}"
        data-notifications-selected-template="{{ __('messages.notifications_selected_count', ['count' => ':count']) }}"
        data-notifications-delete-confirm="{{ __('messages.notifications_delete_confirm') }}"
        data-notifications-action-error="{{ __('messages.notifications_action_error') }}"
    >
        @include('admin.notifications-content')
    </aside>
</div>
