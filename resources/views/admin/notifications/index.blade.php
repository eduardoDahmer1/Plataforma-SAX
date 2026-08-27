@extends('layout.admin')

@section('title', __('messages.notifications_page_title').' - SAX')

@section('content')
@php
    $notificationStyles = [
        'new_order' => ['fa-receipt', 'is-success'],
        'order_processing' => ['fa-gears', 'is-success'],
        'order_shipped' => ['fa-truck', 'is-success'],
        'order_completed' => ['fa-circle-check', 'is-success'],
        'deposit_receipt' => ['fa-file-invoice-dollar', 'is-success'],
        'payment_paid' => ['fa-circle-check', 'is-success'],
        'rendix_refund_request' => ['fa-rotate-left', 'is-warning'],
        'checkout_error' => ['fa-cart-shopping', 'is-danger'],
        'high_value_abandoned_cart' => ['fa-cart-shopping', 'is-warning'],
        'abandoned_cart_feedback' => ['fa-comment-dots', 'is-info'],
        'low_stock' => ['fa-box-open', 'is-warning'],
        'out_of_stock' => ['fa-box', 'is-danger'],
        'new_contact' => ['fa-envelope', 'is-info'],
        'new_resume' => ['fa-file-lines', 'is-info'],
        'new_user' => ['fa-user-plus', 'is-info'],
        'integration_failed' => ['fa-triangle-exclamation', 'is-danger'],
        'integration_stale' => ['fa-plug-circle-xmark', 'is-danger'],
        'integration_recovered' => ['fa-circle-check', 'is-success'],
    ];
    $categoryLabels = [
        'orders' => __('messages.notifications_filter_orders'),
        'payments' => __('messages.notifications_filter_payments'),
        'checkout' => __('messages.notifications_filter_checkout'),
        'clients' => __('messages.notifications_filter_clients'),
        'contacts' => __('messages.notifications_filter_contacts'),
        'catalog' => __('messages.notifications_filter_catalog'),
        'system' => __('messages.notifications_integrations_system'),
    ];
@endphp

<x-admin.card>
    <x-admin.page-header
        title="{{ __('messages.notifications_page_title') }}"
        description="{{ __('messages.notifications_page_subtitle') }}">
        <x-slot:actions>
            @if ($stats['unread'] > 0)
                <form action="{{ route('admin.notifications.read-all') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-dark px-3">
                        <i class="fa-solid fa-check-double me-2"></i>{{ __('messages.notifications_mark_all_read') }}
                    </button>
                </form>
            @endif
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.alert />

    <div class="sax-notification-stats">
        @foreach ([
            ['total', 'notifications_stat_total', 'fa-bell', 'is-blue'],
            ['active', 'notifications_stat_active', 'fa-inbox', 'is-green'],
            ['archived', 'notifications_stat_archived', 'fa-box-archive', 'is-gray'],
            ['unread', 'notifications_stat_unread', 'fa-circle', 'is-red'],
        ] as [$statKey, $labelKey, $icon, $color])
            <div class="sax-notification-stat {{ $color }}">
                <span><i class="fa-solid {{ $icon }}" aria-hidden="true"></i></span>
                <div>
                    <small>{{ __('messages.'.$labelKey) }}</small>
                    <strong>{{ number_format($stats[$statKey], 0, ',', '.') }}</strong>
                </div>
            </div>
        @endforeach
    </div>

    <form method="GET" action="{{ route('admin.notifications.index') }}" class="sax-notification-filters">
        <div class="sax-notification-search">
            <label for="notificationSearch">{{ __('messages.notifications_search_placeholder') }}</label>
            <div>
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                <input type="text" id="notificationSearch" name="search" value="{{ $search }}"
                       placeholder="{{ __('messages.notifications_search_placeholder') }}">
            </div>
        </div>

        <div>
            <label for="notificationCategory">{{ __('messages.notifications_filter_label') }}</label>
            <select id="notificationCategory" name="category" class="form-select">
                <option value="">{{ __('messages.notifications_filter_all') }}</option>
                @foreach ($categoryTypes as $categoryKey => $types)
                    <option value="{{ $categoryKey }}" @selected($category === $categoryKey)>
                        {{ $categoryLabels[$categoryKey] }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="notificationStatus">{{ __('messages.notifications_view_label') }}</label>
            <select id="notificationStatus" name="status" class="form-select">
                <option value="active" @selected($status === 'active')>{{ __('messages.notifications_view_active') }}</option>
                <option value="archived" @selected($status === 'archived')>{{ __('messages.notifications_view_archived') }}</option>
                <option value="all" @selected($status === 'all')>{{ __('messages.notifications_view_all') }}</option>
            </select>
        </div>

        <div>
            <label for="notificationPerPage">{{ __('messages.notifications_limit_label') }}</label>
            <select id="notificationPerPage" name="per_page" class="form-select">
                @foreach ([10, 20, 30, 50, 100] as $option)
                    <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }}</option>
                @endforeach
            </select>
        </div>

        <div class="sax-notification-filter-actions">
            <button type="submit" class="btn btn-dark"><i class="fa-solid fa-filter me-2"></i>{{ __('messages.notifications_apply_filters') }}</button>
            <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary">{{ __('messages.notifications_clear_filters') }}</a>
        </div>
    </form>

    <form action="{{ route('admin.notifications.bulk') }}" method="POST" id="notificationsPageBulkForm"
          class="sax-notification-bulk" data-delete-confirm="{{ __('messages.notifications_delete_confirm') }}">
        @csrf
        <label>
            <input class="form-check-input" type="checkbox" id="notificationsPageSelectAll">
            <span>{{ __('messages.notifications_select_all') }}</span>
        </label>
        <strong id="notificationsPageSelectedCount">{{ __('messages.notifications_selected_count', ['count' => 0]) }}</strong>
        <div>
            <button type="submit" class="btn btn-outline-dark btn-sm" name="action" value="archive" disabled>
                <i class="fa-solid fa-box-archive me-1"></i>{{ __('messages.notifications_archive') }}
            </button>
            <button type="submit" class="btn btn-outline-dark btn-sm" name="action" value="restore" disabled>
                <i class="fa-solid fa-rotate-left me-1"></i>{{ __('messages.notifications_restore') }}
            </button>
            <button type="submit" class="btn btn-outline-danger btn-sm" name="action" value="delete" disabled>
                <i class="fa-regular fa-trash-can me-1"></i>{{ __('messages.notifications_delete') }}
            </button>
        </div>
    </form>

    <div class="sax-notification-page-list">
        @forelse ($notifications as $notification)
            @php
                [$icon, $style] = $notificationStyles[$notification->type] ?? ['fa-bell', 'is-gray'];
                $categoryKey = collect($categoryTypes)
                    ->search(fn ($types) => in_array($notification->type, $types, true));
            @endphp
            <article class="sax-notification-page-item {{ is_null($notification->read_at) ? 'is-unread' : '' }}">
                <label class="sax-notification-page-check" title="{{ __('messages.notifications_select') }}">
                    <input class="form-check-input" type="checkbox" name="notification_ids[]"
                           value="{{ $notification->id }}" form="notificationsPageBulkForm"
                           data-notifications-page-select aria-label="{{ __('messages.notifications_select') }}">
                </label>

                <span class="sax-notification-page-icon {{ $style }}">
                    <i class="fa-solid {{ $icon }}" aria-hidden="true"></i>
                </span>

                <div class="sax-notification-page-copy">
                    <div class="sax-notification-page-heading">
                        <strong>{{ $notification->translatedTitle() }}</strong>
                        <div class="sax-notification-page-badges">
                            @if ($categoryKey !== false)
                                <span>{{ $categoryLabels[$categoryKey] }}</span>
                            @endif
                            @if ($notification->archived_at)
                                <span class="is-archived">{{ __('messages.notifications_status_archived') }}</span>
                            @elseif (is_null($notification->read_at))
                                <span class="is-unread">{{ __('messages.notifications_status_unread') }}</span>
                            @else
                                <span>{{ __('messages.notifications_status_read') }}</span>
                            @endif
                        </div>
                    </div>
                    <p>{{ $notification->translatedMessage() }}</p>
                    <time datetime="{{ $notification->created_at?->toIso8601String() }}">
                        {{ __('messages.notifications_created_at') }} {{ $notification->created_at?->format('d/m/Y H:i') }}
                        · {{ $notification->created_at?->locale(app()->getLocale())->diffForHumans() }}
                    </time>
                </div>

                <div class="sax-notification-page-actions">
                    <form action="{{ route('admin.notifications.read', $notification) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-dark btn-sm">
                            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>{{ __('messages.notifications_open') }}
                        </button>
                    </form>

                    @if (is_null($notification->archived_at))
                        <form action="{{ route('admin.notifications.archive', $notification) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline-dark btn-sm" title="{{ __('messages.notifications_archive') }}">
                                <i class="fa-solid fa-box-archive"></i>
                            </button>
                        </form>
                    @else
                        <form action="{{ route('admin.notifications.restore', $notification) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline-dark btn-sm" title="{{ __('messages.notifications_restore') }}">
                                <i class="fa-solid fa-rotate-left"></i>
                            </button>
                        </form>
                    @endif

                    <form action="{{ route('admin.notifications.destroy', $notification) }}" method="POST"
                          onsubmit='return confirm(@js(__("messages.notifications_delete_confirm")))'>
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm" title="{{ __('messages.notifications_delete') }}">
                            <i class="fa-regular fa-trash-can"></i>
                        </button>
                    </form>
                </div>
            </article>
        @empty
            <div class="sax-notification-page-empty">
                <i class="fa-regular fa-bell-slash" aria-hidden="true"></i>
                <strong>{{ __('messages.notifications_empty_title') }}</strong>
                <span>{{ __('messages.notifications_page_empty') }}</span>
            </div>
        @endforelse
    </div>

    @if ($notifications->hasPages())
        <div class="sax-cat__pag">{{ $notifications->links() }}</div>
    @endif
</x-admin.card>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('notificationsPageBulkForm');
    const selectAll = document.getElementById('notificationsPageSelectAll');
    const checkboxes = Array.from(document.querySelectorAll('[data-notifications-page-select]'));
    const count = document.getElementById('notificationsPageSelectedCount');
    if (!form || !selectAll) return;

    const refresh = () => {
        const selected = checkboxes.filter(checkbox => checkbox.checked).length;
        count.textContent = @js(__('messages.notifications_selected_count', ['count' => ':count'])).replace(':count', selected);
        form.querySelectorAll('button[type="submit"]').forEach(button => button.disabled = selected === 0);
        selectAll.checked = checkboxes.length > 0 && selected === checkboxes.length;
        selectAll.indeterminate = selected > 0 && selected < checkboxes.length;
    };

    selectAll.addEventListener('change', () => {
        checkboxes.forEach(checkbox => checkbox.checked = selectAll.checked);
        refresh();
    });
    checkboxes.forEach(checkbox => checkbox.addEventListener('change', refresh));
    form.addEventListener('submit', event => {
        if (event.submitter?.value === 'delete' && !window.confirm(form.dataset.deleteConfirm)) {
            event.preventDefault();
        }
    });
});
</script>
@endsection
