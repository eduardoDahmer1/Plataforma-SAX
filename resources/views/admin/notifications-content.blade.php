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
        'integration_failed' => ['icon' => 'fa-triangle-exclamation', 'class' => 'is-default'],
        'integration_stale' => ['icon' => 'fa-plug-circle-xmark', 'class' => 'is-default'],
        'integration_recovered' => ['icon' => 'fa-circle-check', 'class' => 'is-order'],
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
        'integration_failed' => 'system',
        'integration_stale' => 'system',
        'integration_recovered' => 'system',
    ];
@endphp

<div class="sax-admin-notifications__header">
    <div>
        <strong id="adminNotificationsTitle">{{ __('messages.notifications_admin_title') }}</strong>
        <span>{{ __('messages.notifications_admin_subtitle') }}</span>
    </div>

    <div class="sax-admin-notifications__header-actions">
        @if ($adminUnreadNotificationsCount > 0)
            <span class="sax-admin-notifications__unread" data-notifications-unread-count>
                {{ $adminUnreadNotificationsCount }} {{ $adminUnreadNotificationsCount === 1 ? __('messages.notifications_unread_singular') : __('messages.notifications_unread_plural') }}
            </span>

            <form action="{{ route('admin.notifications.read-all') }}" method="POST" data-notifications-read-all>
                @csrf
                <button type="submit" class="sax-admin-notifications__read-all">
                    {{ __('messages.notifications_mark_all_read') }}
                </button>
            </form>
        @endif

        <button type="button" class="sax-admin-notifications__close" id="adminNotificationsClose" aria-label="{{ __('messages.notifications_close') }}">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
    </div>
</div>

<div class="px-3 py-2 border-bottom">
    <label class="visually-hidden" for="adminNotificationsFilter">{{ __('messages.notifications_filter_label') }}</label>
    <select class="form-select form-select-sm" id="adminNotificationsFilter">
        <option value="all">{{ __('messages.notifications_filter_all') }}</option>
        <option value="orders">{{ __('messages.notifications_filter_orders') }}</option>
        <option value="payments">{{ __('messages.notifications_filter_payments') }}</option>
        <option value="checkout">{{ __('messages.notifications_filter_checkout') }}</option>
        <option value="clients">{{ __('messages.notifications_filter_clients') }}</option>
        <option value="contacts">{{ __('messages.notifications_filter_contacts') }}</option>
        <option value="catalog">{{ __('messages.notifications_filter_catalog') }}</option>
        <option value="system">{{ __('messages.notifications_integrations_system') }}</option>
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
                    <strong>{{ $notification->translatedTitle() }}</strong>
                    <span>{{ $notification->translatedMessage() }}</span>
                    <time datetime="{{ $notification->created_at?->toIso8601String() }}">
                        {{ $notification->created_at?->locale(app()->getLocale())->diffForHumans() }}
                    </time>
                </span>

                @if (is_null($notification->read_at))
                    <span class="sax-admin-notifications__dot" aria-label="{{ __('messages.notifications_unread_singular') }}"></span>
                @endif
            </button>
            @if (is_null($notification->read_at))
                <button type="button" class="sax-admin-notifications__mark-read"
                        data-notification-mark-read title="{{ __('messages.notifications_mark_read') }}">
                    <i class="fa-solid fa-check" aria-hidden="true"></i>
                    <span>{{ __('messages.notifications_mark_read') }}</span>
                </button>
            @endif
        </form>
    @empty
        <div class="sax-admin-notifications__empty">
            <i class="fa-regular fa-bell-slash" aria-hidden="true"></i>
            <strong>{{ __('messages.notifications_empty_title') }}</strong>
            <span>{{ __('messages.notifications_admin_empty_message') }}</span>
        </div>
    @endforelse

    <div class="sax-admin-notifications__empty d-none" id="adminNotificationsFilteredEmpty">
        <i class="fa-regular fa-bell-slash" aria-hidden="true"></i>
        <strong>{{ __('messages.notifications_filtered_empty_title') }}</strong>
        <span>{{ __('messages.notifications_admin_filtered_empty_message') }}</span>
    </div>
</div>
