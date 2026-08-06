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
    <div><strong id="adminNotificationsTitle">{{ __('messages.notifications_customer_title') }}</strong><span>{{ __('messages.notifications_customer_subtitle') }}</span></div>
    <div class="sax-admin-notifications__header-actions">
        @if ($customerUnreadNotificationsCount > 0)
            <span class="sax-admin-notifications__unread" data-notifications-unread-count>{{ $customerUnreadNotificationsCount }} {{ $customerUnreadNotificationsCount === 1 ? __('messages.notifications_unread_singular') : __('messages.notifications_unread_plural') }}</span>
            @if ($customerPersistedUnreadNotificationsCount > 0)
                <form action="{{ route('user.notifications.read-all') }}" method="POST" data-notifications-read-all>
                    @csrf
                    <button type="submit" class="sax-admin-notifications__read-all">{{ __('messages.notifications_mark_all_read') }}</button>
                </form>
            @endif
        @endif
        <button type="button" class="sax-admin-notifications__close" id="adminNotificationsClose" aria-label="{{ __('messages.notifications_close') }}"><i class="fa-solid fa-xmark"></i></button>
    </div>
</div>

<div class="px-3 py-2 border-bottom">
    <label class="visually-hidden" for="adminNotificationsFilter">{{ __('messages.notifications_filter_label') }}</label>
    <select class="form-select form-select-sm" id="adminNotificationsFilter">
        <option value="all">{{ __('messages.notifications_filter_all') }}</option>
        <option value="orders">{{ __('messages.notifications_filter_my_orders') }}</option>
        <option value="payments">{{ __('messages.notifications_filter_payments') }}</option>
        <option value="security">{{ __('messages.notifications_filter_security') }}</option>
        <option value="account">{{ __('messages.notifications_filter_account') }}</option>
        <option value="coupons">{{ __('messages.notifications_filter_coupons') }}</option>
        <option value="news">{{ __('messages.notifications_filter_news') }}</option>
        <option value="updates">{{ __('messages.catalog_purchase_paused_title') }}</option>
    </select>
</div>

<div class="sax-admin-notifications__list">
    @foreach ($customerOperationalAlerts as $alert)
        <div class="sax-admin-notifications__operational" data-notification-item
             data-notification-category="{{ $alert['category'] }}" data-operational-alert>
            <div class="sax-admin-notifications__item">
                <span class="sax-admin-notifications__icon is-update"><i class="fa-solid {{ $alert['icon'] }}"></i></span>
                <span class="sax-admin-notifications__content">
                    <strong>{{ $alert['title'] }}</strong>
                    <span>{{ $alert['message'] }}</span>
                </span>
                <span class="sax-admin-notifications__dot" aria-hidden="true"></span>
            </div>
        </div>
    @endforeach

    @foreach ($customerNotifications as $notification)
        @php($category = $categories[$notification->type] ?? 'account')
        <form action="{{ route('user.notifications.read', $notification) }}" method="POST"
              data-notification-item data-notification-category="{{ $category }}">
            @csrf
            <button type="submit" class="sax-admin-notifications__item {{ is_null($notification->read_at) ? 'is-unread' : '' }}">
                <span class="sax-admin-notifications__icon is-user"><i class="fa-solid {{ $icons[$category] ?? 'fa-bell' }}"></i></span>
                <span class="sax-admin-notifications__content">
                    <strong>{{ $notification->translatedTitle() }}</strong><span>{{ $notification->translatedMessage() }}</span>
                    <time datetime="{{ $notification->created_at?->toIso8601String() }}">{{ $notification->created_at?->locale(app()->getLocale())->diffForHumans() }}</time>
                </span>
                @if (is_null($notification->read_at))<span class="sax-admin-notifications__dot" aria-label="{{ __('messages.notifications_unread_singular') }}"></span>@endif
            </button>
            @if (is_null($notification->read_at))
                <button type="button" class="sax-admin-notifications__mark-read"
                        data-notification-mark-read title="{{ __('messages.notifications_mark_read') }}">
                    <i class="fa-solid fa-check" aria-hidden="true"></i>
                    <span>{{ __('messages.notifications_mark_read') }}</span>
                </button>
            @endif
        </form>
    @endforeach

    @if ($customerOperationalAlerts->isEmpty() && $customerNotifications->isEmpty())
        <div class="sax-admin-notifications__empty"><i class="fa-regular fa-bell-slash"></i><strong>{{ __('messages.notifications_empty_title') }}</strong><span>{{ __('messages.notifications_customer_empty_message') }}</span></div>
    @endif
    <div class="sax-admin-notifications__empty d-none" id="adminNotificationsFilteredEmpty">
        <i class="fa-regular fa-bell-slash"></i><strong>{{ __('messages.notifications_filtered_empty_title') }}</strong><span>{{ __('messages.notifications_customer_filtered_empty_message') }}</span>
    </div>
</div>
