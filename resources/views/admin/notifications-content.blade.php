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
        'rendix_refund_request' => ['icon' => 'fa-rotate-left', 'class' => 'is-order'],
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
        'rendix_refund_request' => 'payments',
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
        <a href="{{ route('admin.notifications.index') }}" class="sax-admin-notifications__view-page">
            {{ __('messages.notifications_open_page') }}
        </a>

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

<div class="sax-admin-notifications__filters">
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

    <div class="sax-admin-notifications__filter-row">
        <label class="visually-hidden" for="adminNotificationsView">{{ __('messages.notifications_view_label') }}</label>
        <select class="form-select form-select-sm" id="adminNotificationsView">
            <option value="active">{{ __('messages.notifications_view_active') }}</option>
            <option value="archived">{{ __('messages.notifications_view_archived') }}</option>
            <option value="all">{{ __('messages.notifications_view_all') }}</option>
        </select>

        <label class="visually-hidden" for="adminNotificationsLimit">{{ __('messages.notifications_limit_label') }}</label>
        <select class="form-select form-select-sm" id="adminNotificationsLimit">
            <option value="30">{{ __('messages.notifications_limit_count', ['count' => 30]) }}</option>
            <option value="20">{{ __('messages.notifications_limit_count', ['count' => 20]) }}</option>
            <option value="10">{{ __('messages.notifications_limit_count', ['count' => 10]) }}</option>
            <option value="all">{{ __('messages.notifications_limit_all') }}</option>
        </select>
    </div>
</div>

<form action="{{ route('admin.notifications.bulk') }}" method="POST"
      class="sax-admin-notifications__bulk" id="adminNotificationsBulkForm">
    @csrf
    <label class="sax-admin-notifications__select-all">
        <input type="checkbox" id="adminNotificationsSelectAll">
        <span>{{ __('messages.notifications_select_all') }}</span>
    </label>
    <span data-notifications-selected-count>{{ __('messages.notifications_selected_count', ['count' => 0]) }}</span>
    <div class="sax-admin-notifications__bulk-actions">
        <button type="submit" name="action" value="archive" data-notifications-bulk-action="archive" disabled>
            <i class="fa-solid fa-box-archive" aria-hidden="true"></i>
            {{ __('messages.notifications_archive') }}
        </button>
        <button type="submit" name="action" value="restore" data-notifications-bulk-action="restore" disabled>
            <i class="fa-solid fa-rotate-left" aria-hidden="true"></i>
            {{ __('messages.notifications_restore') }}
        </button>
        <button type="submit" name="action" value="delete" class="is-danger"
                data-notifications-bulk-action="delete" disabled>
            <i class="fa-regular fa-trash-can" aria-hidden="true"></i>
            {{ __('messages.notifications_delete') }}
        </button>
    </div>
</form>

<div class="sax-admin-notifications__list">
    @forelse ($adminNotifications as $notification)
        @php
            $style = $notificationStyles[$notification->type]
                ?? ['icon' => 'fa-bell', 'class' => 'is-default'];
            $category = $notificationCategories[$notification->type] ?? 'other';
        @endphp

        <div class="sax-admin-notifications__row"
             data-notification-item
             data-notification-id="{{ $notification->id }}"
             data-notification-category="{{ $category }}"
             data-notification-archived="{{ is_null($notification->archived_at) ? '0' : '1' }}">
            <label class="sax-admin-notifications__checkbox" title="{{ __('messages.notifications_select') }}">
                <input type="checkbox" value="{{ $notification->id }}" data-notification-select
                       aria-label="{{ __('messages.notifications_select') }}">
            </label>

            <form action="{{ route('admin.notifications.read', $notification) }}" method="POST"
                  data-notification-read-form>
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
            </form>

            <div class="sax-admin-notifications__item-actions">
                @if (is_null($notification->read_at))
                    <button type="button" data-notification-mark-read title="{{ __('messages.notifications_mark_read') }}">
                        <i class="fa-solid fa-check" aria-hidden="true"></i>
                        <span>{{ __('messages.notifications_mark_read') }}</span>
                    </button>
                @endif

                <form action="{{ route('admin.notifications.archive', $notification) }}" method="POST"
                      class="{{ is_null($notification->archived_at) ? '' : 'd-none' }}"
                      data-notification-action="archive">
                    @csrf
                    <button type="submit" title="{{ __('messages.notifications_archive') }}">
                        <i class="fa-solid fa-box-archive" aria-hidden="true"></i>
                        <span>{{ __('messages.notifications_archive') }}</span>
                    </button>
                </form>

                <form action="{{ route('admin.notifications.restore', $notification) }}" method="POST"
                      class="{{ is_null($notification->archived_at) ? 'd-none' : '' }}"
                      data-notification-action="restore">
                    @csrf
                    <button type="submit" title="{{ __('messages.notifications_restore') }}">
                        <i class="fa-solid fa-rotate-left" aria-hidden="true"></i>
                        <span>{{ __('messages.notifications_restore') }}</span>
                    </button>
                </form>

                <form action="{{ route('admin.notifications.destroy', $notification) }}" method="POST"
                      data-notification-action="delete">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="is-danger" title="{{ __('messages.notifications_delete') }}">
                        <i class="fa-regular fa-trash-can" aria-hidden="true"></i>
                        <span>{{ __('messages.notifications_delete') }}</span>
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div class="sax-admin-notifications__empty" id="adminNotificationsEmpty">
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
