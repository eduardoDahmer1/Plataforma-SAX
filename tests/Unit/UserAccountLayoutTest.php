<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class UserAccountLayoutTest extends TestCase
{
    public function test_account_uses_a_dedicated_customer_workspace(): void
    {
        $layout = file_get_contents(__DIR__.'/../../resources/views/layout/dashboard.blade.php');

        $this->assertStringContainsString('sax-account-shell', $layout);
        $this->assertStringContainsString('sax-account-rail', $layout);
        $this->assertStringContainsString('sax-account-topbar', $layout);
        $this->assertStringContainsString('sax-user-menu-drawer', $layout);
        $this->assertStringContainsString("route('home')", $layout);
        $this->assertStringContainsString("route('cart.view')", $layout);
        $this->assertStringContainsString('users.notifications-menu', $layout);
        $this->assertStringContainsString('locale-currency-selector', $layout);
        $this->assertStringNotContainsString('headerPartial', $layout);
        $this->assertStringNotContainsString('<x-components.footer', $layout);
    }

    public function test_account_navigation_is_reusable_without_duplicate_modal_markup(): void
    {
        $menu = file_get_contents(__DIR__.'/../../resources/views/components/users/menu.blade.php');

        $this->assertStringContainsString("'recentAbandonedCarts' => null", $menu);
        $this->assertStringContainsString('@if($showNavigation)', $menu);
        $this->assertStringContainsString('@if($showModal)', $menu);
        $this->assertStringContainsString("request()->routeIs('user.abandoned-carts.*')", $menu);
        $this->assertStringContainsString('aria-label="Navegação da conta"', $menu);
    }

    public function test_account_styles_define_neutral_and_responsive_tokens(): void
    {
        $styles = file_get_contents(__DIR__.'/../../public/css/user.css');

        $this->assertStringContainsString('Customer account workspace', $styles);
        $this->assertStringContainsString('--account-sidebar: #34373b', $styles);
        $this->assertStringContainsString('--account-page: #f3f3f1', $styles);
        $this->assertStringContainsString('@media (max-width: 991.98px)', $styles);
        $this->assertStringContainsString('@media (max-width: 479.98px)', $styles);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $styles);
    }

    public function test_customer_notification_actions_stay_inside_each_notification_row(): void
    {
        $notifications = file_get_contents(__DIR__.'/../../resources/views/users/notifications-content.blade.php');
        $styles = file_get_contents(__DIR__.'/../../public/css/user.css');

        $this->assertStringContainsString('class="sax-admin-notifications__row"', $notifications);
        $this->assertStringContainsString('data-notification-read-form', $notifications);
        $this->assertStringContainsString('class="sax-admin-notifications__item-actions"', $notifications);
        $this->assertStringContainsString('[data-notification-mark-read]', $styles);
        $this->assertStringContainsString('grid-row: 1 / span 2', $styles);
    }
}
